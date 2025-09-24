(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).ready(function() {

		// Initialize public functionality
		CDSWerxPublic.init();

	});

	/**
	 * CDSWerx Public Object
	 */
	var CDSWerxPublic = {

		/**
		 * Initialize public functionality
		 */
		init: function() {
			this.bindEvents();
			this.initComponents();
			this.initLazyLoading();
		},

		/**
		 * Bind public events
		 */
		bindEvents: function() {
			// Handle button clicks
			$(document).on('click', '.cdswerx-btn', this.handleButtonClick);

			// Handle form submissions
			$(document).on('submit', '.cdswerx-form', this.handleFormSubmit);

			// Handle toggle elements
			$(document).on('click', '.cdswerx-toggle', this.handleToggle);

			// Handle accordion
			$(document).on('click', '.cdswerx-accordion-header', this.handleAccordion);

			// Handle modals
			$(document).on('click', '[data-cdswerx-modal]', this.handleModalOpen);
			$(document).on('click', '.cdswerx-modal-close', this.handleModalClose);

			// Handle tabs
			$(document).on('click', '.cdswerx-tab-nav a', this.handleTabs);

			// Window events
			$(window).on('scroll', this.handleScroll);
			$(window).on('resize', this.handleResize);
		},

		/**
		 * Initialize components
		 */
		initComponents: function() {
			this.initSliders();
			this.initCounters();
			this.initAnimations();
			this.initTooltips();
		},

		/**
		 * Initialize sliders
		 */
		initSliders: function() {
			$('.cdswerx-slider').each(function() {
				var $slider = $(this);
				var options = $slider.data('options') || {};

				// Basic slider functionality
				var currentSlide = 0;
				var $slides = $slider.find('.cdswerx-slide');
				var totalSlides = $slides.length;

				if (totalSlides > 1) {
					// Create navigation
					var $nav = $('<div class="cdswerx-slider-nav"></div>');
					$nav.append('<button class="cdswerx-slider-prev">‹</button>');
					$nav.append('<button class="cdswerx-slider-next">›</button>');
					$slider.append($nav);

					// Navigation events
					$slider.on('click', '.cdswerx-slider-next', function() {
						currentSlide = (currentSlide + 1) % totalSlides;
						CDSWerxPublic.updateSlider($slider, currentSlide);
					});

					$slider.on('click', '.cdswerx-slider-prev', function() {
						currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
						CDSWerxPublic.updateSlider($slider, currentSlide);
					});

					// Auto-play
					if (options.autoplay) {
						setInterval(function() {
							currentSlide = (currentSlide + 1) % totalSlides;
							CDSWerxPublic.updateSlider($slider, currentSlide);
						}, options.interval || 5000);
					}
				}
			});
		},

		/**
		 * Update slider position
		 */
		updateSlider: function($slider, slideIndex) {
			var $slides = $slider.find('.cdswerx-slide');
			$slides.removeClass('active').eq(slideIndex).addClass('active');
		},

		/**
		 * Initialize counters
		 */
		initCounters: function() {
			$('.cdswerx-counter').each(function() {
				var $counter = $(this);
				var target = parseInt($counter.data('target')) || 0;
				var duration = parseInt($counter.data('duration')) || 2000;

				// Animate counter on scroll into view
				var observer = new IntersectionObserver(function(entries) {
					entries.forEach(function(entry) {
						if (entry.isIntersecting) {
							CDSWerxPublic.animateCounter($counter, target, duration);
							observer.unobserve(entry.target);
						}
					});
				});

				observer.observe($counter[0]);
			});
		},

		/**
		 * Animate counter
		 */
		animateCounter: function($counter, target, duration) {
			var start = 0;
			var startTime = Date.now();

			function updateCounter() {
				var elapsed = Date.now() - startTime;
				var progress = Math.min(elapsed / duration, 1);
				var current = Math.floor(start + (target - start) * progress);

				$counter.text(current);

				if (progress < 1) {
					requestAnimationFrame(updateCounter);
				}
			}

			updateCounter();
		},

		/**
		 * Initialize animations
		 */
		initAnimations: function() {
			$('.cdswerx-animate').each(function() {
				var $element = $(this);
				var animation = $element.data('animation') || 'fadeIn';

				var observer = new IntersectionObserver(function(entries) {
					entries.forEach(function(entry) {
						if (entry.isIntersecting) {
							$element.addClass('animated ' + animation);
							observer.unobserve(entry.target);
						}
					});
				});

				observer.observe($element[0]);
			});
		},

		/**
		 * Initialize tooltips
		 */
		initTooltips: function() {
			$('[data-tooltip]').each(function() {
				var $element = $(this);
				var tooltipText = $element.data('tooltip');

				$element.on('mouseenter', function() {
					var $tooltip = $('<div class="cdswerx-tooltip">' + tooltipText + '</div>');
					$('body').append($tooltip);

					var offset = $element.offset();
					$tooltip.css({
						top: offset.top - $tooltip.outerHeight() - 10,
						left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
					});
				});

				$element.on('mouseleave', function() {
					$('.cdswerx-tooltip').remove();
				});
			});
		},

		/**
		 * Initialize lazy loading
		 */
		initLazyLoading: function() {
			$('.cdswerx-lazy').each(function() {
				var $img = $(this);

				var observer = new IntersectionObserver(function(entries) {
					entries.forEach(function(entry) {
						if (entry.isIntersecting) {
							var src = $img.data('src');
							if (src) {
								$img.attr('src', src).removeClass('cdswerx-lazy');
								observer.unobserve(entry.target);
							}
						}
					});
				});

				observer.observe($img[0]);
			});
		},

		/**
		 * Handle button clicks
		 */
		handleButtonClick: function(e) {
			var $btn = $(this);

			// Add click effect
			$btn.addClass('clicked');
			setTimeout(function() {
				$btn.removeClass('clicked');
			}, 150);

			// Handle special button types
			if ($btn.hasClass('cdswerx-btn-loading')) {
				$btn.prop('disabled', true).addClass('loading');
			}
		},

		/**
		 * Handle form submissions
		 */
		handleFormSubmit: function(e) {
			var $form = $(this);
			var $submitBtn = $form.find('input[type="submit"], button[type="submit"]');

			// Basic validation
			var isValid = true;
			$form.find('[required]').each(function() {
				if (!$(this).val()) {
					isValid = false;
					$(this).addClass('error');
				} else {
					$(this).removeClass('error');
				}
			});

			if (!isValid) {
				e.preventDefault();
				CDSWerxPublic.showMessage('Please fill in all required fields', 'error');
			} else {
				// Show loading state
				$submitBtn.prop('disabled', true).addClass('loading');
			}
		},

		/**
		 * Handle toggle elements
		 */
		handleToggle: function(e) {
			e.preventDefault();
			var $toggle = $(this);
			var target = $toggle.data('target');

			if (target) {
				$(target).slideToggle();
				$toggle.toggleClass('active');
			}
		},

		/**
		 * Handle accordion
		 */
		handleAccordion: function(e) {
			e.preventDefault();
			var $header = $(this);
			var $content = $header.next('.cdswerx-accordion-content');
			var $accordion = $header.closest('.cdswerx-accordion');

			// Close other accordion items
			$accordion.find('.cdswerx-accordion-content').not($content).slideUp();
			$accordion.find('.cdswerx-accordion-header').not($header).removeClass('active');

			// Toggle current item
			$content.slideToggle();
			$header.toggleClass('active');
		},

		/**
		 * Handle modal open
		 */
		handleModalOpen: function(e) {
			e.preventDefault();
			var modalId = $(this).data('cdswerx-modal');
			var $modal = $('#' + modalId);

			if ($modal.length) {
				$modal.addClass('active');
				$('body').addClass('modal-open');
			}
		},

		/**
		 * Handle modal close
		 */
		handleModalClose: function(e) {
			e.preventDefault();
			$(this).closest('.cdswerx-modal').removeClass('active');
			$('body').removeClass('modal-open');
		},

		/**
		 * Handle tabs
		 */
		handleTabs: function(e) {
			e.preventDefault();
			var $link = $(this);
			var target = $link.attr('href');
			var $tabNav = $link.closest('.cdswerx-tab-nav');
			var $tabContent = $tabNav.siblings('.cdswerx-tab-content');

			// Update navigation
			$tabNav.find('a').removeClass('active');
			$link.addClass('active');

			// Update content
			$tabContent.find('.cdswerx-tab-pane').removeClass('active');
			$tabContent.find(target).addClass('active');
		},

		/**
		 * Handle scroll events
		 */
		handleScroll: function() {
			var scrollTop = $(window).scrollTop();

			// Back to top button
			if (scrollTop > 300) {
				$('.cdswerx-back-to-top').addClass('visible');
			} else {
				$('.cdswerx-back-to-top').removeClass('visible');
			}

			// Sticky elements
			$('.cdswerx-sticky').each(function() {
				var $element = $(this);
				var offset = $element.data('offset') || 0;

				if (scrollTop > offset) {
					$element.addClass('stuck');
				} else {
					$element.removeClass('stuck');
				}
			});
		},

		/**
		 * Handle resize events
		 */
		handleResize: function() {
			// Responsive adjustments
			CDSWerxPublic.adjustResponsive();
		},

		/**
		 * Adjust responsive elements
		 */
		adjustResponsive: function() {
			var windowWidth = $(window).width();

			// Mobile adjustments
			if (windowWidth < 768) {
				$('.cdswerx-desktop-only').hide();
				$('.cdswerx-mobile-only').show();
			} else {
				$('.cdswerx-desktop-only').show();
				$('.cdswerx-mobile-only').hide();
			}
		},

		/**
		 * Show message
		 */
		showMessage: function(message, type) {
			type = type || 'info';
			var $message = $('<div class="cdswerx-message cdswerx-message-' + type + '">' + message + '</div>');

			$('body').append($message);

			setTimeout(function() {
				$message.addClass('visible');
			}, 10);

			setTimeout(function() {
				$message.removeClass('visible');
				setTimeout(function() {
					$message.remove();
				}, 300);
			}, 4000);
		},

		/**
		 * Utility: Debounce function
		 */
		debounce: function(func, wait) {
			var timeout;
			return function executedFunction() {
				var later = function() {
					clearTimeout(timeout);
					func.apply(this, arguments);
				};
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
			};
		}

	};

	// Make CDSWerxPublic globally available
	window.CDSWerxPublic = CDSWerxPublic;

})( jQuery );
