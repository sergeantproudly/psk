<?php
namespace Engine\Library\Ajax;

use Engine\Library\Mail;
use Engine\Library\UserSession;
use Respect\Validation\Validator as v;
use Engine\Library\CatalogCart;
use Site\Models\CatalogModel;
use Site\Models\UserModel;
use Site\Pages\CertificatesPage;
use Site\Pages\RegisterPage;

/**
 * Класс, инкапсулирующий константы с причинами ошибки выполнения AJAX вопросов
 * 
 * При ответе сервера с ошибкой, будет передан один из этих кодов, определяющих этап, на котором произошла ошибка. На клиентской стороне можно привязываться к получаемым значениям этих кодов и отображать соответствующее значение.
 * 
 * Циферные коды выбраны по причине того, что сравнение строковых кодов больше подвержено ошибкам (например, опечатки в строке) или проблемам изменения строки кода на стороне сервера (например, из-за клиентской привязки нельзя вносить правки в строки кодов)
 */


class AjaxHandler {

  public function execute($action, $params) {

    if (!method_exists($this, $action)) {
      return false;
    }

    return $this->{$action}($params);
  }

  public function setItem($params) {
    // TODO: Добавить проверку авторизации

    $id = $params['id'];
    $amount = $params['amount'];

    $cart = new CatalogCart();


    $added = $cart->set($id, $amount);

    if ($added)
      return Event::success(null, compact('id', 'amount'), 'Товар успешно добавлен в коризну');
    else
      return Event::fail(Errors::FAIL, compact('id', 'amount'), 'Не удалось добавить товар');
  }

  public function removeItem($params) {
    // TODO: Добавить проверку авторизации

    $id = $params['id'];
    $cart = new CatalogCart();
    $removed = $cart->remove($id);

    if ($removed)
      return Event::success(null, compact('id'));
    else
      return Event::fail(Errors::FAIL, compact('id'), 'Указанный товар в корзине не найден');
  }

  public function logIn ($params) {
    global $Database;
    $userSession = new UserSession();

    $email = $params['email'];
    $password = $params['password'];

    if ($userSession->isLoggedIn())
      return Event::fail(Errors::FAIL, [
        'userId' => $userSession->id(),
        'email' => $email,
        'loggedIn' => true
      ], 'Для повторной авторизации необходимо выйти из аккаунта');

    $userModel = new UserModel($Database);

    $user = $userModel->getUser($email, $password);
    if ($user) {

      $userSession->logIn($user['Id']);

      return Event::success(null, [
        'userId' => $user['Id'],
        'email' => $email,
        'loggedIn' => true
      ]);
    } else {
      return Event::fail(Errors::FAIL, [
        'email' => $email,
        'loggedIn' => false
      ], 'Неверная пара e-mail/пароль');
    }
  }
//    $passwordHashed = $userModel->getPassword($email);
////    return Event::success(null, ['request' => $request]);
//    return Event::success(null, [
//      'user' => $user,
//      'password' => $password,
//      'verify' => password_verify($password, $passwordHashed),
//      'email' => $email,
//    ]);

//    return Event::success(null, [
//      'user' => $user,
//      'logged' => $user->isLoggedIn(),
//      'id' => $user->id(),
//      'cookie' => $user->cookie()]);
//  }

  public function logOut() {
    $userSession = new UserSession();

    if (!$userSession->isLoggedIn())
      return Event::fail(Errors::FAIL, [
//        'loggedId' => $userSession->id(),
        'loggedOut' => false,
//        'loggedCookie' => $userSession->cookie()
      ], 'Для выхода из аккаунта необходимо быть авторизованным');

    $userSession->logOut();
//    print_r($userSession);
    if (!$userSession->isLoggedIn()) {
      return Event::success(null, [
        'loggedOut' => true
      ]);
    } else {
      return Event::fail(Errors::FAIL, [
          'loggedId' => $userSession->id(),
          'loggedOut' => false,
          'loggedCookie' => $userSession->cookie()
        ]);
    }
  }

  public function makeOrder($params) {
    global $Database;

    $userSession = new UserSession();
    $userId = $userSession->id();

    if (!$userSession->isLoggedIn())
      return Event::fail(Errors::FAIL, [
//        'userId' => $userId,
        'userId' => $userSession->id()
      ], 'Перед отправкой заявки необходимо авторизоваться');

//    if ($userId != $userSession->id())
//      return Event::fail(Errors::FAIL, [
//        'userId' => $userId,
////        'loggedId' => $userSession->id()
//      ], 'Полученный пользовательский Id и авторизованный Id не совпадают');

    $userModel = new UserModel($Database);
    $user = $userModel->getUserById($userId);

    if (!$user) {
      return Event::fail(Errors::FAIL, ['userId' => $user,], 'Пользователь с полученным Id не найден');
    }

//    $cart = new CatalogCart;
    $catalogCart = new CatalogCart;
    $catalogModel = new CatalogModel($Database);
    $cart = $catalogModel->getItems($catalogCart->getAll());

    if ($cart['count'] === 0)
      return Event::fail(Errors::FAIL, ['userId' => $userId, 'cart' => $cart], 'В корзине нет товаров');
    $msg = Mail::prepareOrder($user, $cart['items']);
    $isSent = Mail::sendToAdmin($msg['Subject'], $msg['Html'], $msg['Text']);
    // $isSent = true;

    $saved = $catalogModel->saveOrder($userId, $cart['items']);

    $response = Event::success(null, [
      'user' => $user,
      'cart' => $cart,
      'isSent' => $isSent,
      'saved' => $saved,
      'mail' => $html
    ], 'Заявка успешно отправлена');

    $catalogCart->cleanAll();
    return $response;
  }

  public function getCart() {
    global $Database;

    $cart = new CatalogCart;
    $cartItems = $cart->getAll();

    $catalogModel = new CatalogModel($Database);
    $catalogItems = $catalogModel->getItems($cartItems);

//    var_dump($cartItems);

    if (isset($catalogItems['items']) && count($catalogItems['items']) != 0)
      return Event::success(null, $catalogItems, 'Список товаров успешно получен');

    if (count($catalogItems['items']) == 0)
      return Event::success(null, $catalogItems, 'В корзине нет товаров');

    return Event::fail(Errors::FAIL, ['sessionItems' => $cartItems, 'dbItems' => $catalogItems]);
  }

  public function cleanCart() {
    $cart = new CatalogCart;

    $cart->cleanAll();

    return Event::success(null, $cart->getAll(), 'Корзина успешно очищена');
  }

  public function getItem($params) {
    global $Database;

    $id = $params['id'];
    $catalogModel = new CatalogModel($Database);
    $item = $catalogModel->getItemById($id);

    if ($item) {
      return Event::success(null, $item);
    } else {
      return Event::fail(Errors::FAIL, [], 'Товар не найден');
    }
  }

  public function getRegister ($params) {
    $registerPage = new RegisterPage();
    
    $view = $registerPage->getContentSorted($params);
    if ($view) {
      return Event::success(null, ['params' => $params, 'html' => $view], 'Отсортированные данные успешно возвращены');
    } else {
      return Event::success(Errors::FAIL, ['params' => $params]);
    }
  }

  public function getCertificates($params) {
    $certPage = new CertificatesPage();

    $selectBy = $params['select'] ?? null;

    $sortBy = $params['sort']['by'] ?? null;
    $sortDirection = $params['sort']['direction'] ?? null;

    $sortedView = $certPage->getContentSorted($params);

    // // Require sort and select
    // if ($selectBy && $sortBy) {

    //   return Event::success(null, $params, 'Отсортированная выборка успешно возвращена');

    // } else if ($selectBy) {

    //   return Event::success(null, $params, 'Выборка данных успешно возвращена');

    // } else if ($sortBy) {

    //   return Event::success(null, $params, 'Отсортированные данные успешно возвращены');

    // }
    if ($sortedView) {
      return Event::success(null, ['params' => $params, 'html' => $sortedView], 'Отсортированные данные успешно возвращены');
    }

    return Event::fail(Errors::FAIL, $params,
      'Не указано поле для сортировки и не установлено значение для select');
  }

  public function addQuestion() {
    $name = trim($_POST['name']);
    $tel = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $company = trim($_POST['company']);
    $text = $_POST['message'];
    $code = $_POST['code'];
    $serviceId = $_POST['service_id'];

    $capcha = $_POST['capcha'];

    // spambot check
    // based on the collation of user agents
    if ($capcha == $_SERVER['HTTP_USER_AGENT']) {
    //if (true) {
      if ($name && $email) {
        $request = '';
        if ($name) $request .= "Имя: $name\r\n";
        if ($email) $request .= "E-mail: $email\r\n";
        if ($tel) $request .= "Телефон: $tel\r\n";
        if ($company) $request .= "Компания: $company\r\n";
        if ($text) $request .= 'Сообщение:'."\r\n$text\r\n";
        if ($serviceId) {
          $service = $this->db->getRow('SELECT Title FROM services WHERE Id = ?i', $serviceId);
          $request .= 'Услуга: ' . $service['Title'] . "\r\n";
        }
        // $this->db->query('INSERT INTO requests SET DateTime=NOW(), Form = ?s, Name = ?s, Tel = ?s, Email = ?s, Company = ?s, Text = ?s, RefererPage = ?s, IsSet = 0',
        //   $form ? $form['Title'] : 'Написать нам',
        //   $name,
        //   $tel,
        //   $email,
        //   $company,
        //   str_replace('"', '\"', $request),
        //   $_SERVER['HTTP_REFERER']
        // );

        // TABLES['CALLBACK']
        $table = 'data_user-requests';
        $name = $validated['name'];
        $email = $validated['email'];
        $description = $validated['text'];

        global $Database;
        $request = $Database->query("INSERT INTO `{$table}` (`Form`, `Name`, `Tel`, `Email`, `Text`, `DateTime`, `Company`, `RefererPage`, `IsSet`) VALUES ('" . ($form ? $form['Title'] : 'Написать нам') . "', '{$name}', '{$tel}', '{$email}', '{$text}', NOW(), '{$company}', '" . $_SERVER['HTTP_REFERER'] . "', 0)");
          
        // global $Config;
        // $siteTitle = strtr(stGetSetting('SiteEmailTitle', $Config['Site']['Title']), array('«'=>'"','»'=>'"','—'=>'-'));
        // $siteEmail = stGetSetting('SiteEmail', $Config['Site']['Email']);
        // $adminTitle = 'Administrator';
        // $adminEmail = stGetSetting('RequestsEmail', $Config['Site']['Email']);
          
        // $letter['subject'] = $form['Title'].' form "'.$siteTitle.'" website';
        // $letter['html'] = '<b>'.$form['Title'].'</b><br/><br/>';
        // $letter['html'] .= str_replace("\r\n", '<br/>', $request);
        // $mail = new Mail();
        // $mail->SendMailFromSite($adminEmail, $letter['subject'], $letter['html']);
        
        $json = array(
          'status' => true,
          'header' => $form ? $form['SuccessHeader'] : 'Спасибо!',
          'message' => $form ? $form['Success'] : 'Мы приняли ваше сообщение и свяжемся с вами!',
        );

      } else {
        $json = array(
          'status' => false,
          'message' => 'Произошла ошибка отправки. При ее повторении, пожалуйста, свяжитесь с нами по телефону',
        );
      }
    } else {
      $json = array(
        'status' => false,
        'message' => 'Произошла ошибка отправки. При ее повторении, пожалуйста, свяжитесь с нами по телефону',
      );
    }

    return json_encode($json);
  }
}
  
//   public function addQuestion() {
//     $data = $_POST['data'];
//     $noise = $_POST['noise'];
//     $php_default_uniqid_length = 13;

//     if (empty($noise) || strlen($noise) !== $php_default_uniqid_length) {
//       return Event::fail(Errors::FAIL, [
//         'data' => $data,
//         'noise' => $noise,
//       ], 'Бот не может отправлять сообщения');
//     }

//     $botData = array_filter($data, function ($pair) use ($noise) {
//       $fieldHasNoise = mb_strpos($pair['name'], $noise) !== false;
//       $valueIsEmpty = empty($pair['value']);
//       return ($fieldHasNoise && $valueIsEmpty) || (!$fieldHasNoise && !$valueIsEmpty);
//     });
//     $isBot = count($botData) !== 0;

//     $realData = array_filter($data, function ($pair) use ($noise) {
//       $fieldHasNoise = mb_strpos($pair['name'], $noise) !== false;
//       return $fieldHasNoise;
//     });

//     $data = [];
    
//     foreach ($realData as $key => $pair) {
//       $name = str_replace($noise, '', $pair['name']);
//       $value = $pair['value'];
//       $data[$name] = $value;
//     }

//     if ($isBot) {
//       return Event::fail(Errors::FAIL, [
//         'realData' => $data, 
//         'botData' => $botData,
//         'isBot' => $isBot,
//       ], 'Бот не может отправлять сообщения');
//     }

//     $validators = [
//       'email' => v::email(),
//       'phone' => v::phone(),
//       'name' => v::stringType(),
//       'text' => v::stringType(),
//     ];

//     $invalid = [];
//     $validated = [];

//     foreach ($data as $name => $value) {
//       $value = trim(strip_tags($value));

//       if (isset($validators[$name]) && !$validators[$name]->validate($value)) {
//         $invalid[] = $name;
//         continue;
//       }

//       $validated[$name] = $value;
//     }

//     if ($invalid) {
//       return Event::validationFail($invalid, 'Пожалуйста, перепроверьте правильность заполнения полей и повторите отправку.');
//     }

//     //$msg = Mail::prepareCallback($validated);
//     //$isSent = Mail::sendToAdmin($msg['Subject'], $msg['Html'], $msg['Text']);
//      $isSent = true;

//     // TABLES['CALLBACK']
//     $table = 'data_user-requests';
//     $name = $validated['name'];
//     $email = $validated['email'];
//     $description = $validated['text'];

//     global $Database;

//     $request = $Database->query("INSERT INTO `{$table}` (`Name`, `Email`, `Description`, `Date`) VALUES ('{$name}', '{$email}', '{$description}', NOW())");

//     if (!$isSent) {
//       return Event::sendingFail([], 'Невозможно отправить сообщение при помощи функции SendMail()');
//     }

//     return Event::success(null, ['validated' => $validated, 'data' => $data, 'isBot' => $isBot,]);
//   }
// }
