var __widthMobile = 1000;
var __widthMobileTablet = 1024;
var __widthMobileTabletMiddle = 768;
var __widthMobileTabletSmall = 600;
var __widthMobileSmall = 540;
var __isMobile = ($(window).width() <= __widthMobile);
var __isMobileTablet = ($(window).width() <= __widthMobileTablet);
var __isMobileTabletMiddle = ($(window).width() <= __widthMobileTabletMiddle);
var __isMobileTabletSmall = ($(window).width() <= __widthMobileTabletSmall);
var __isMobileSmall = ($(window).width() <= __widthMobileSmall);
var __animationSpeed = 350;	



$(document).ready(function(){

	$.fn.lightTabs = function() {
		var showTab = function(tab, saveHash) {;
			if (!$(tab).hasClass('tab-act')) {
				var tabs = $(tab).closest('.tabs');

				var target_id = $(tab).attr('href');
		        var old_target_id = $(tabs).find('.tab-act').attr('href');
		        $(target_id).show();
		        $(old_target_id).hide();
		        $(tabs).find('.tab-act').removeClass('tab-act');
		        $(tab).addClass('tab-act');

		        if (typeof(saveHash) != 'undefined' && saveHash) history.pushState(null, null, target_id);
			}
		}

		var initTabs = function() {
            var tabs = this;
            
            $(tabs).find('a').each(function(i, tab){
                $(tab).click(function(e) {
                	e.preventDefault();

                	showTab(this, true);
                	fadeoutInit();

                	return false;
                });
                if (i == 0) showTab(tab);                
                else $($(tab).attr('href')).hide();
            });	

            $(tabs).swipe({
				swipeStatus: function(event, phase, direction, distance) {
					var offset = distance;

					if (phase === $.fn.swipe.phases.PHASE_START) {
						var origPos = $(this).scrollLeft();
						$(this).data('origPos', origPos);

					} else if (phase === $.fn.swipe.phases.PHASE_MOVE) {
						var origPos = $(this).data('origPos');

						if (direction == 'left') {
							var scroll_max = $(this).prop('scrollWidth') - $(this).width();
							var scroll_value_new = origPos - 0 + offset;
							$(this).scrollLeft(scroll_value_new);
							if (scroll_value_new >= scroll_max) $(this).addClass('scrolled-full');
							else $(this).removeClass('scrolled-full');

						} else if (direction == 'right') {
							var scroll_value_new = origPos - offset;
							$(this).scrollLeft(scroll_value_new);
							$(this).removeClass('scrolled-full');
						}

					} else if (phase === $.fn.swipe.phases.PHASE_CANCEL) {
						var origPos = $(this).data('origPos');
						$(this).scrollLeft(origPos);

					} else if (phase === $.fn.swipe.phases.PHASE_END) {
						$(this).data('origPos', $(this).scrollLeft());
					}
				},
				threshold: 70
			});	
        };

        return this.each(initTabs);
    };

	initElements();

    // BURGER
	$('nav').click(function() {
		if (__isMobile && !$('body').hasClass('mobile-opened')) {
			if (!$('header').children('.close').data('inited')) {
				if (!$('header>.close').length) {
					$('header').append('<div class="close"></div>');
				}
				$('header').children('.close').click(function(e) {
					e.stopPropagation();

					$('body').removeClass('mobile-opened');
					$('html').removeClass('html-mobile-long');
					$('#layout').height('auto').removeClass('js-modal-overflow');
					//$('.modal-fadeout').stop().fadeOut(300);
				}).data('inited', true);
			}

			$('body').addClass('mobile-opened');

			var innerHeight = $('nav').outerHeight();
			if (innerHeight > $(window).height()) {
				$('html').addClass('html-mobile-long');
			} else {
				$('html').removeClass('html-mobile-long');
			}

			$('#layout').addClass('js-modal-overflow').height($('header').outerHeight());

			//$('.modal-fadeout').stop().fadeIn(300);
		}
	});
	$('nav ul>li.has-child>a').click(function(e) {
		if (__isMobile) {
			e.preventDefault();

			var innerHeight = $('nav').outerHeight();

			if (!$(this).parent().hasClass('opened')) {
				$(this).parent().addClass('opened').children('ul').stop().slideDown(__animationSpeed*2, function() {
					$('html').toggleClass('html-mobile-long', innerHeight > $(window).height());
					$('#layout').addClass('js-modal-overflow').height($('nav').outerHeight());
				});
			} else {
				$(this).parent().removeClass('opened').children('ul').stop().slideUp(__animationSpeed, function() {
					$('html').toggleClass('html-mobile-long', innerHeight > $(window).height());
					$('#layout').addClass('js-modal-overflow').height($('nav').outerHeight());
				});
			}
		}
	});

	// MODAL LINKS
	$('.js-modal-link').click(function(e) {
		e.preventDefault();
		showModal($(this).attr('href').substring(1));
	});

	// SLICKS
	$('.js-slider').each(function(i, slider) {
		var mobile = $(slider).attr('data-mobile');
		var adaptive = $(slider).attr('data-adaptive');
		var dots = $(slider).attr('data-dots') === 'false' ? false : true;
		var arrows = $(slider).attr('data-arrows') === 'true' ? true : false;
		var autoplay = $(slider).attr('data-autoplay') ? $(slider).attr('data-autoplay') : false;
		var slidesToShow = adaptive ? Math.floor($(slider).outerWidth() / $(slider).children('li, .li').outerWidth()) : 1;
		var infinite = $(slider).attr('data-infinite') === 'false' ? false : true;
		var center = $(slider).attr('data-center') === 'false' ? false : true;
	
		if (mobile) {
			if ((mobile === 'true' && __isMobile) ||
				(mobile === 'middle' && __isMobileTabletMiddle) ||
				(mobile === 'small' && __isMobileTabletSmall) ||
				(mobile === 'mobile' && __isMobileSmall)) {					
	
				$(slider).slick({
					slidesToShow: slidesToShow,
					slidesToScroll: slidesToShow,
					dots: dots,
					arrows: arrows,
					autoplay: autoplay,
					centerMode: center,
     				centerPadding: '0',
     				infinite: infinite
				});
			}
		} else {
			$(slider).slick({
				slidesToShow: slidesToShow,
				slidesToScroll: slidesToShow,
				dots: dots,
				arrows: arrows,
				autoplay: autoplay,
				centerMode: center,
     			centerPadding: '0',
     			infinite: infinite
			});
		}
	});

	// LIGHTBOXES
	var galleries = new Array();
	$('.js-lightbox').each(function(i, a) {
		if (!$(a).is('[data-gallery]')) {
			$(a).magnificPopup({
				type: 'image',
				removalDelay: 300,
				callbacks: {
			        beforeOpen: function() {
			            $(this.contentContainer).removeClass('fadeOut').addClass('animated fadeIn');
			        },
			        beforeClose: function() {
			        	$(this.contentContainer).removeClass('fadeIn').addClass('fadeOut');
			        }
			    },
				midClick: true
			});
		} else {
			if (typeof(galleries[$(a).attr('data-gallery')]) == 'undefined') galleries.push($(a).attr('data-gallery'));
		}
	});
	$.each(galleries, function(i, gallery) {
		$('.js-lightbox[data-gallery="' + gallery + '"]').magnificPopup({
			type: 'image',
			removalDelay: 300,
			callbacks: {
		        beforeOpen: function() {
		             $(this.contentContainer).removeClass('fadeOut').addClass('animated fadeIn');
		        },
		        beforeClose: function() {
		        	$(this.contentContainer).removeClass('fadeIn').addClass('fadeOut');
		        }
		    },
			gallery: {
				enabled: true
			},
			midClick: true
		});
	});

  // ANIMATE NUMBERS
    $('.js-num-animated').each(function() {
      var num = parseInt($(this).text().replace(/[^\d]/g, ''));
      var delay = $(this).attr('data-delay') ? $(this).attr('data-delay') - 0 : 0;

      $(this).html($(this).text().replace(num, '<span>' + num + '</span>'));

      $(this).children('span').animateNumber({
        number: num
      },
      {
        easing: 'swing',
        duration: __animationSpeed*1.5 + delay
      });
    });

	// TATARSTAN MAP ANIMATED
	if ($('#map-animated').length) {
		var maAutoplaySpeed = $('#map-animated').attr('data-speed') * 1000;

		var statesCount = 3;
		var currState = 0;
		var prevState;
		var paths = [];

    var tickCurrent = 0;
    var ticksLimit = 2;
    var tickInterval = null;

		for (var i = currState; i < statesCount; i++) {
			paths.push('assets/images/map_state' + i + '.svg');
		}
    paths.push('assets/images/map_state_clear.svg');

		$.preloadImages(paths, function() {
			tickInterval = setInterval(function() {
        if (tickCurrent < ticksLimit) {
          prevState = currState;
          if (currState < statesCount - 1) currState++;
          else currState = 0;
          //$('#map-animated').addClass('state' + currState).removeClass('state' + prevState);
          $('#state' + prevState).animate({opacity: 0}, __animationSpeed*0.8);
          $('#state' + currState).animate({opacity: 0.8}, __animationSpeed*0.8);
          tickCurrent++;

        } else {
          //$('#map-animated').addClass('state-clear').removeClass('state' + prevState);
          $('#state' + currState).animate({opacity: 0}, __animationSpeed*0.8);
          $('#state-clear').animate({opacity: 0.75}, __animationSpeed*0.8);
          $('#kazan').fadeIn(__animationSpeed*1.5);
          $('#map-animated .contacts').delay(__animationSpeed).addClass('slideIn');
          clearInterval(tickInterval);
        }
			}, maAutoplaySpeed);
		});
	}

	// BLOCK SLIDER
	if ($('#bl-slider').length) {
		resizeCallbacks.push(function() {
    		var autoplaySpeed = $('#bl-slider').attr('data-speed') * 1000;
			var padd = Math.round(($(window).width() - $('header>.holder').width()) / 2);
			var paddings = !__isMobileSmall ? padd : 0;

			$('#bl-slider ul').on('init', function(e, $slick) {
				var $slide = $($slick.$slides[$slick.currentSlide]);
				$slide.addClass('animated');
			});

			$('#bl-slider ul').slick({
				slidesToShow: 1,
				slidesToScroll: 1,
				dots: true,
				arrows: false,
				autoplay: true,
				autoplaySpeed: autoplaySpeed,
				infinite: true,
				centerMode: true,
				centerPadding: paddings + 'px',
				pauseOnHover: false
			});
			$('#bl-slider ul').on('afterChange', function(e, $slick, currentSlide) {
				var prevSlide = currentSlide == 1 ? $slick.slideCount : currentSlide - 2;
				var $curr = $($slick.$slides[currentSlide]);
				var $prev = $($slick.$slides[prevSlide]);
				$curr.addClass('animated');
				$prev.removeClass('animated');
			});
    	});
	}

	// MAP
    if ($('#map').length) {
    	var placeholderSrc = 'https://psk-si.ru/assets/images/map_placeholder.png';
      var placeholderCoords = [55.655072, 49.212550];
      	ymaps.ready(function () {
        	var map = new ymaps.Map('map', {
	          center: placeholderCoords,
	          zoom: 14,
	          controls: ['zoomControl']
	        });
	        map.behaviors.disable('scrollZoom');
	        var mark = new ymaps.Placemark(placeholderCoords, {}, {
	          iconLayout: 'default#imageWithContent',
	          iconImageHref: placeholderSrc,
            iconImageSize: __widthMobileSmall ? [60, 65] : [83, 90],
            iconImageOffset: __widthMobileSmall ? [-20, -65] : [-25, -90]
	        });

        	map.geoObjects.add(mark);
      });
    }

    // PRODUCTS
    if ($('#production').length) {
    	$('#production .products>li .question').click(function() {
    		if (__isMobile) {
    			$(this).closest('li').toggleClass('opened');
    		}
    	});
    }

    // NEW
    if ($('#new').length) {
    	resizeCallbacks.push(function() {
    		if (__isMobile) {
    			$('#new .photo').insertAfter('#new h1, #new .h1');
    		} else {
    			$('#new .photo').prependTo('#new');
    		}
    	});
    }

    onResize();


    /** MODES 13.06.2018 */

    // AUTH
    if ($('.js-auth-form').length) {
      $('.js-auth-form').submit(function (e) {
        e.preventDefault();

        var form = this;
        msgUnset(form);
        if (checkElements([form.email, form.password], [{1: true, 2: true}, {1: true}], 0)) {
          form.submit_btn.disabled = true;
          var waitNode = msgSetWait(form);
          $.ajax({
            type : $(form).attr('method'),
            url  : $(form).attr('action'),
            data : $(form).serialize(),
            dataType: 'json',
            success: function (response) {
              if (!response.failed) {
                reload();

              } else {
                msgSetError(form, response.msg);
              }
              $(waitNode).remove();
              form.submit_btn.disabled = false;
            }
          });
        } else {
          msgSetError(form, 'Пожалуйста, введите e-mail и пароль.');
        }

        return false;
      });
    }
    if ($('#bl-profile .logout').length) {
      $('#bl-profile .logout').on('click', function (e) {
        e.preventDefault();

        $.ajax({
          type: 'POST',
          url: $(this).attr('href'),
          data: {},
          dataType: 'json',
          success: function (response) {
            if (!response.failed) {
              reload();
            } else {
              alert(response.msg);
            }
          }
        });

        return false;
      });
    }

    $('table.private .sort').on('click', function () {
      var self = this;

      // FIXME
      $(self).closest('table').find('th').each(function (i, th) {
        if (th != self) {
          $(th).removeClass('act rev');
        }
      });
      if (!$(self).hasClass('rev')) {
        $(self).addClass('act rev');
      } else {
        $(self).removeClass('rev');
      }
    });

    $('.filter select').selectmenu({
      change: function (e, ui) {
        if ($(ui.item.element).val() == 0) {
          $(this).selectmenu('widget').addClass('init');
        } else {
          $(this).selectmenu('widget').removeClass('init');
        }

        // FIXME
      }
    });

    $('.filter .ui-selectmenu-button').addClass('init');

    var $search = $('#js-search');

    $search.on('submit', function (e) {
      e.preventDefault();

      var query = $search.find('input').val();
      var action = $search.attr('action');

      console.log(action + query + '/');

      redirect(action + query + '/');
    });

    var $logInButtons = $('.js-log-in');
    var $authPopup = $('.js-auth-popup');

    $logInButtons.on('click', function () {
      $authPopup.show();
    });

    var $sortButtons = $('.js-sort');
    var $regTable = $('.js-register-tbody');
    var $certTable = $('.js-certificates-tbody');
    var $pagination = $('.js-pagination');

    // if (!window.select) {
    //   window.select = false;
    // }

    // sortHandler() gets an arg through jQuery .on() second param,
    // which is available as event.data.param1 inside the handler
    var sortHandler = function (event) {
      var $tableBody = event.data.$tableBody;
      var link = event.data.link;
      var $button = $(this);

      console.log($tableBody);

      window.sort = {
        'by': $button.attr('data-sort-by'),
        'direction': $button.hasClass('rev') ? 'asc' : 'desc'
      };

      if (!window.select) {
        window.select = getSelectList();
      }

      var data = {
        select: window.select, // may be a complex select with 2 filters at the same time
        sort: window.sort
      };

      $.ajax({
        type: 'POST',
        url: link,
        data: data,
        success: function (response) {
          console.log(response);

          var paginationHTMLString = response.data['html']['Pagination'];
          var tableHTMLString = response.data['html']['Table'];

          if ($tableBody.length) {
            
            $tableBody.html(tableHTMLString);

            if ($pagination.length) {
              $newPaignation = $(paginationHTMLString);
              $pagination.html($newPaignation.html());
            }
          }
        }, 
        error: function (error) {
          console.log(error.responseText);
        }
      });
    };

    // console.log($certTable);
    if ($regTable.length) {

      $sortButtons.on('click', {$tableBody: $regTable, link: '/register/get-data/'}, sortHandler);

    } else if ($certTable.length) {
      // console.log('Добавляем к сертификатам');
      $sortButtons.on('click', {$tableBody: $certTable, link: '/certificates/get-data/'}, sortHandler);
    }

    var $form = $('.js-form-question');

    var notification = new function() {
      this.$node = $('.js-form-status');
      
      this.show = function (msg) {
        this.$node.show();
        this.$node.text(msg);
      };

      this.hide = function () {
        this.$node.hide();
        this.$node.text('');
      };
    }


    if ($form.length) {
      var fields = [];
      
      $form.find('.js-form-input').on('blur', function (e) {
        var $field = $(this);
        
        var field = new function () {
          this.$node = $field;
          this.valid = function () {
          	/*
            this.$node.css({
              'border-bottom': '2px solid rgb(72, 208, 88)'
            });
            */
            this.$node.removeClass('invalid');
          }
          this.invalid = function () {
          	/*
            this.$node.css({
              'border-bottom': '2px solid #E00022',
            });
            */
            this.$node.addClass('invalid');
          };

          this.clean = function () {
            this.$node.css('border-bottom', '')
          }
        }
        var value = $field.val();

        fields.push(field);
        field.clean();

        // TODO: Add validation support
        // Primitive validation
        if (!value || value.length === 0) {
          field.invalid();
        } else {
          field.valid();
        }
      })
      
      var $closeButton = $('#js-popup-question-close');

      $closeButton.on('click', function () {
        $closeButton.parent('.js-popup-question').fadeOut();
        
        return false;
      })

      $form.on('submit', function () {
        var $fields = $form.find(':input');

        var $requiredFields = $fields.filter(function() {
          return this.hasAttribute('data-form-input');
        });

        var $biteFields = $fields.filter(function() {
          return !this.hasAttribute('data-form-input');
        });

        var noise = $requiredFields[0].name.substring(0, 13);

        var data = $requiredFields.serializeArray();
        var invalids = [];

        // 1. Check whether all fields are filled (except for checkbox that's tested seperately)
        if (!data || data.length < 3) {
          notification.show('Извините, необходимо заполнить все поля для отправки сообщения');

          return false;
        }

        // 3. Iterate each field value and check for emptyness
        data.forEach(function(piece) {
          if (!piece['value'] || piece['value'].length === 0) {
            invalids.push(piece);
          }
        });

        if (invalids.length !== 0) {
          notification.show('Извините, необходимо заполнить все поля для отправки сообщения');

          return false;
        }

        var $agree = $('.js-form-checkbox');
        var isAgree = $agree.prop('checked');

        // 2. Check required agreement for data processing
        if (!isAgree) {
          // console.log('Changing message to agreement\'s message');
          notification.show('Извините, для отправки сообщения нам необходимо ваше разрешение на обработку данных');

          return false;
        } else {
          notification.hide();
        }

        // Get the full data (with fields that visible only for bots)
        data = data.concat($biteFields.serializeArray());
        data = {
          'data': data,
          'noise': noise
        };

        $.ajax({
          type: $form.attr('method'),
          url:  $form.attr('action'),
          data: data,
          success: function (response) {
            if (response.failed) {
              if (response.code == 2) {
                notification.show(response.msg);
              } else {
                alert(response.msg);
              }
              return; 
            }

            notification.show('Мы получили ваше сообщение! В ближайшее время с вами свяжется наш менеджер.');
            
            fields.forEach(function (field) {
              field.clean();
            })

            $form.trigger('reset');
          },
          error: function (error) {
            console.log(error.responseText);
          }
        });

        return false;
      })
    }

    /** /MODES 13.06.2018 */

    /** MODES 22.04.2021 */
    if ($('#catalogue.certs').length) {
      $('#catalogue.certs .js-toggler').click(function() {
        $(this).closest('tr').find('.hidden').slideDown(__animationSpeed);
        $(this).hide();
      });
    }
    /** MODES 22.04.2021 */

    /** MODES 15.06.2021 */
    // ANCHORS
    $('.js-anchor').click(function(e) {
    	e.preventDefault();
    	_scrollTo($(this).attr('href'), -$(window).height() * 0.1);
    });

    // STORES
		if ($('#stores').length) {
			function storesCheckTitles() {
				if (__isMobileTabletMiddle) {
					$('#stores .navigation ul>li>a').each(function(i, item) {
						if ($(item).text().indexOf('Набережные') != -1) {
							$(item).text($(item).text().replace('Набережные', 'Наб.'));
						}
					});
				} else {
					$('#stores .navigation ul>li>a').each(function(i, item) {
						if ($(item).text().indexOf('Наб.') != -1) {
							$(item).text($(item).text().replace('Наб.', 'Набережные'));
						}
					});
				}
			}
			resizeCallbacks.push(function() {
				storesCheckTitles();
			});
			storesCheckTitles();
		}
		/** MODES 15.06.2021 */

		/** MODES 14.11.2021 */
		if ($('#bl-video>a').length) {
			var lang = getLanguageGroup();

			$('#bl-video>a').click(function(e) {
				var player = $('#js-youtube-video').data('player');
				if (lang == 'ru') {
					var code = $(this).attr('data-video-code-rus');
				} else {
					var code = $(this).attr('data-video-code-eng');
				}
				new YT.Player('js-youtube-video', {
					height: '100%',
			    width: '100%',
				  videoId: code,
				  playerVars: { 'autoplay': 1, 'controls': 0 }
				});
				player.loadVideoById(code);
				player.playVideo();

				if ($('#video-lang-swticher').length) {
					$('#video-lang-swticher ul>li>a[data-lang="' + lang + '"]').addClass('curr').parent().siblings('li').children('a.curr').removeClass('curr');
					$('#video-lang-swticher ul>li>a').click(function(e) {
						e.preventDefault();

						if (!$(this).hasClass('curr')) {
							var code = $(this).attr('href');
							player.loadVideoById(code);
							player.playVideo();
							$(this).addClass('curr').parent().siblings('li').children('a.curr').removeClass('curr');
						}
					});
				}
			});

			$('#modal-video .modal-close').click(function() {
				var player = $('#js-youtube-video').data('player');
				player.pauseVideo();
				player.stopVideo();
			});
		}
		/** MODES 14.11.2021 */

});