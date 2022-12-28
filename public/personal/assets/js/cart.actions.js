var $cart = $('.js-cart');
var $cartPopup = $('#layout-cart');
var $catalog = $('#catalogue');
var $cartTable = $cartPopup.find('.js-cart-table');
var hidingDelay = 4000;

if ($cart.length) {
  // console.log($cart);

  var $cartCount = $cart.find('.js-cart-count');
  var $cartAmount = $cart.find('.js-cart-amount');
  var $cartMessage = $cart.find('.js-cart-message');

  var isEmpty = $cart[0].hasAttribute('data-cart-empty');

  if (!isEmpty) {
    setTimeout(function() {
      $cart.stop().fadeIn(__animationSpeed*2);
    }, 750);
  }

  // UPDATED: data-amount now automatically added by server on catalog page request, so elements
  // have data-amount attr by default

  // Init cart
  catInit();

  $('.js-catalog-item-add').on('click', cartAddItemHandler);

  $('#cart-edit').on('click', function (e) {
    e.preventDefault();
    // FIXME
    $cartPopup.stop().fadeIn(__animationSpeed);
  });

  $cartPopup.find('.ico-close').on('click', function() {
    $cartPopup.stop().fadeOut(__animationSpeed);
  });

  // console.log($('.js-cart-make-order'));

  $('.js-cart-make-order').on('click', makeOrderHandler);

  $('.js-cart-make-order-quick').on('click', makeQuickOrderHandler);
}

// Click is deprecated since jQuery 3.3
function cartAddItemHandler() {
  var amount = $(this).siblings('input[type="number"]').val();

  // console.log($(this).siblings('input[type="number"]'));
  console.log(amount);

  if (amount) {
    var id = $(this).closest('tr').attr('data-item-id');

    catSetItem(id, amount, function() {
      catRefresh();
      $cart.stop().fadeIn(__animationSpeed*2);
    });
  }
}

function catSetItem(id, amount, callback) {
  $.ajax({
    type: 'get',
    url: '/cart/set-item/' + id + '/' + amount + '/',
    success: function(response) {
      console.log(response);
      if (!response.failed) {
        if (typeof(callback) === 'function') {
          callback();
        }
        return true;

      } else {
        if (response.code == 1) {
          alert('Произошла серверная ошибка');
        } else if (response.code == 2) {
          alert('Проверьте правильность заполнения полей');
        } else if (response.code == 3) {
          alert('Не удалось отправить сообщение. Пожалуйста, свяжитесь с администратором.');
        }
        return false;
      }
    },
    error: function (response) {
      console.log(response.responseText);
    }
  });
}

function catRemoveItem(id, callback) {
  $.ajax({
    type: 'get',
    url: '/cart/remove-item/' + id + '/',
    success: function(response) {
      if (!response.failed) {
        if (typeof(callback) === 'function') callback();
        return true;

      } else {
        if (response.code == 1) {
          alert('Произошла серверная ошибка');
        } else if (response.code == 2) {
          alert('Проверьте правильность заполнения полей');
        } else if (response.code == 3) {
          alert('Не удалось отправить сообщение. Пожалуйста, свяжитесь с администратором.');
        }
        return false;
      }
    }
  });
}

function catInit() {
  $cartPopup.find('.js-cart-item-update').on('keyup keypress blur change', function() {
    var id = $(this).closest('tr').attr('data-item-id');
    var amount = $(this).val();

    if (amount !== parseInt($(this).attr('data-amount'))) {
      catSetItem(id, amount, function() {
        catRefresh();
      });
    }
  });

  $cartPopup.find('.js-cart-item-remove').on('click', function() {
    var id = $(this).closest('tr').attr('data-item-id');

    catRemoveItem(id, function() {
      catRefresh();
    });
  });
}

function catRefresh() {
  $.ajax({
    type: 'get',
    url: '/cart/get-item/all/',
    success: function(response) {
      if (!response.failed) {
        var data = response.data;

        var countMessage = data.count + ' ' + declOfNum(data.count, ['наименование', 'наименования', 'наименований']);
        var amountMessage = data.totalAmount + ' ' + declOfNum(data.totalAmount, ['позиция', 'позиции', 'позиций']);

        var emptyCartMessage = 'нет товаров';
        var message = $cartMessage.text();


        if (data.count) {
          if (message === emptyCartMessage) {
            $cartMessage.text('');

            $cartMessage.append($cartCount);
            $cartMessage.append(' ');
            $cartMessage.append($cartAmount);
          }
          // console.log($cartCount, $cartAmount);

          $cartCount.text(countMessage);
          $cartAmount.text(amountMessage);
        } else {

          if (message !== emptyCartMessage) {
            $cartAmount.text('');
            $cartCount.text('');

            $cartMessage.text(emptyCartMessage);
          }
        }

        $cartTable.find('tr').remove();

        var table = '';
        $.each(data.items, function(i, item){
          table +=
            '<tr data-item-id="' + item.Id + '">' +
            '<td>' + item.Mnemocode + '</td>' +
            '<td>' + item.Title + '</td>' +
            '<td class="count">' +
            '<input class="js-cart-item-update" name="count" value="' + item.Amount + '" type="number" data-amount="' + item.Amount + '">' +
            ' шт. ' +
            '<button class="js-cart-item-remove btn">' +
            '<img src="/images/ico_cart_remove.svg" alt="Удалить" title="Удалить">' +
            '</button>' +
            '</td>' +
            '</tr>';
        });
        console.log(table);
        $cartTable.append(table);

        catInit($cartPopup);

      } else {
        if (response.code == 1) {
          alert('Произошла серверная ошибка');
        } else if (response.code == 2) {
          alert('Проверьте правильность заполнения полей');
        } else if (response.code == 3) {
          alert('Не удалось отправить сообщение. Пожалуйста, свяжитесь с администратором.');
        }
        return false;
      }
    }
  });
}

function makeQuickOrderHandler() {
  var $cartContainer = $cart;
  makeOrder($cartContainer);

  setTimeout(function () {
    $cartContainer.fadeOut(2500, function () {
      $cartContainer.html(
        '<div class="holder">' +
        '<span class="count">В корзине: ' +
        '<strong><span class="js-cart-message">' +
        '<span class="js-cart-count">нет товаров</span>' +
        '<span class="js-cart-amount"></span></span>' +
        '</strong>' +
        '</span>' +
        '<a href="#" class="edit" id="cart-edit">Редактировать</a>' +
        '<button class="js-cart-make-order-quick btn" id="cart-order-quick">Отправить заявку</button>' +
        '</div>');

      $('#cart-edit').on('click', function (e) {
        e.preventDefault();
        // FIXME
        $cartPopup.stop().fadeIn(__animationSpeed);
      });

      $cartPopup.find('.ico-close').on('click', function() {
        $cartPopup.stop().fadeOut(__animationSpeed);
      });

      $cartCount = $cart.find('.js-cart-count');
      $cartAmount = $cart.find('.js-cart-amount');
      $cartMessage = $cart.find('.js-cart-message');

      $('.js-cart-make-order-quick').on('click', makeQuickOrderHandler);

      catRefresh();
    });
  }, hidingDelay)
}

function makeOrderHandler() {
    var $cartContainer = $cartPopup.find('.js-cart-container');
    makeOrder($cartContainer);

    setTimeout(function () {
      $cart.hide();
      $cartPopup.fadeOut(2500, function () {
        $cartContainer.html(
          '<div class="h1">Корзина</div>' +
          '<table class="private" cellpadding="0" cellspacing="0"> ' +
          '<thead>' +
          '<tr>' +
          '<th>Мнемокод</th>' +
          '<th>Наименование</th>' +
          '<th>Редактирование</th>' +
          '</tr>' +
          '</thead>' +
          '<tbody class="js-cart-table"></tbody>' +
          '</table>' +
          '<div class="btn-line">' +
          '<button class="js-cart-update btn cancel" id="cart-save-changes" type="button">Сохранить изменения</button>' +
          '<button class="js-cart-make-order btn" id="cart-order" type="submit" data-user-id="{{USER:ID}}">Отправить заявку</button>' +
          '</div>');
        catRefresh();
        $('.js-cart-make-order').on('click', makeOrderHandler);
        $cartTable = $cartPopup.find('.js-cart-table');
      });
    }, hidingDelay)
}

function makeOrder($cart, event) {
  $.ajax({
    type: 'POST',
    url: '/cart/make-order/',
    success: function(response) {
      if (!response.failed) {
        var msg = 'Мы уже приняли ваш заказ, менеджер свяжется с вами в ближайшее время.';
        if ($cart.hasClass('holder')) {
          $cart.html('<span class="order-success">' + msg + '</span>');
        } else {
          $cart.children('.holder').html('<span class="order-success">' + msg + '</span>');
        }
      } else {
        if (response.code == 1) {
          alert('Произошла серверная ошибка');
        } else if (response.code == 2) {
          alert('Проверьте правильность заполнения полей');
        } else if (response.code == 3) {
          alert('Не удалось отправить сообщение. Пожалуйста, свяжитесь с администратором.');
        }
      }
    },
    error: function (error) {
      console.log(error.responseText);
    }
  });
}
