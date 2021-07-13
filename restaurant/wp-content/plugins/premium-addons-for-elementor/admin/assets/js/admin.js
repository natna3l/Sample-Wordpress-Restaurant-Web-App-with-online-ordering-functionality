(function ($) {

    "use strict";

    var redHadfontLink = document.createElement('link');
    redHadfontLink.rel = 'stylesheet';
    redHadfontLink.href = 'https://fonts.googleapis.com/css?family=Red Hat Display:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';
    redHadfontLink.type = 'text/css';
    document.head.appendChild(redHadfontLink);

    var poppinsfontLink = document.createElement('link');
    poppinsfontLink.rel = 'stylesheet';
    poppinsfontLink.href = 'https://fonts.googleapis.com/css?family=Poppins:100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';
    poppinsfontLink.type = 'text/css';
    document.head.appendChild(poppinsfontLink);

    var settings = premiumAddonsSettings.settings;

    window.PremiumAddonsNavigation = function () {

        var self = this,
            $tabs = $(".pa-settings-tab"),
            $elementsTabs = $(".pa-elements-tab");

        self.init = function () {

            if (!$tabs.length) {
                return;
            }

            self.initNavTabs($tabs);

            self.initElementsTabs($elementsTabs);

            self.handleElementsActions();

            self.handleFormSubmit();

            self.handleRollBack();

            self.handlePaproActions();

        };

        // Handle settings form submission
        self.handleFormSubmit = function () {

            var ajaxData = {
                'pa-settings': 'pa_elements_settings',
                'pa-features': 'pa_elements_settings',
                'pa-integrations': 'pa_additional_settings',
                'pa-ver-control': 'pa_additional_settings'
            };

            $('form.pa-settings-form').on(
                'submit',
                function (e) {

                    var $form = $(this),
                        id = $form.attr("id");

                    e.preventDefault();

                    var action = ajaxData[id];

                    if (!action) {
                        return;
                    }

                    if ('pa_additional_settings' === action) {
                        $form = $('form#pa-ver-control, form#pa-integrations');
                    } else {
                        $form = $('form#pa-settings, form#pa-features');
                    }

                    $.ajax(
                        {
                            url: settings.ajaxurl,
                            type: 'POST',
                            data: {
                                action: action,
                                security: settings.nonce,
                                fields: $form.serialize(),
                            },
                            success: function (response) {

                                console.log(response);

                                Swal.fire(
                                    {
                                        type: 'success',
                                        title: 'Settings Saved!',
                                        footer: 'Have Fun :-)',
                                        showConfirmButton: false,
                                        timer: 1000
                                    }
                                );
                            },
                            error: function (err) {

                                console.log(err);

                                Swal.fire(
                                    {
                                        type: 'error',
                                        title: 'Oops...',
                                        text: 'Something went wrong!'
                                    }
                                );

                            }
                        }
                    );
                }
            );

        };

        // Handle global enable/disable buttons
        self.handleElementsActions = function () {

            $(".pa-elements-filter select").on(
                'change',
                function () {
                    var filter = $(this).val(),
                        $activeTab = $(".pa-switchers-container").not(".hidden");

                    $activeTab.find(".pa-switcher").removeClass("hidden");

                    if ('free' === filter) {
                        $activeTab.find(".pro-element").addClass("hidden");
                    } else if ('pro' === filter) {
                        $activeTab.find(".pa-switcher").not(".pro-element").addClass("hidden");
                    }
                }
            );

            // Enable/Disable all widgets
            $(".pa-btn-group").on(
                "click",
                '.pa-btn',
                function () {

                    var $btn = $(this),
                        isChecked = $btn.hasClass("pa-btn-enable");

                    if (!$btn.hasClass("active")) {
                        $(".pa-btn-group .pa-btn").removeClass("active");
                        $btn.addClass("active");

                        $.ajax(
                            {
                                url: settings.ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'pa_save_global_btn',
                                    security: settings.nonce,
                                    isGlobalOn: isChecked
                                }
                            }
                        );

                    }

                    $("#pa-modules .pa-switcher input").prop("checked", isChecked);

                }
            );

            $("#pa-modules .pa-switcher input").on(
                'change',
                function () {
                    var $this = $(this),
                        id = $this.attr('id'),
                        isChecked = $this.prop('checked');

                    $("input[name='" + id + "']").prop('checked', isChecked);
                }
            )

        };

        // Handle Tabs Elements
        self.initElementsTabs = function ($elem) {

            var $links = $elem.find('a'),
                $sections = $(".pa-switchers-container");

            $sections.eq(0).removeClass("hidden");
            $links.eq(0).addClass("active");

            $links.on(
                'click',
                function (e) {

                    e.preventDefault();

                    var $link = $(this),
                        href = $link.attr('href');

                    // Set this tab to active
                    $links.removeClass("active");
                    $link.addClass("active");

                    // Navigate to tab section
                    $sections.addClass("hidden");
                    $("#" + href).removeClass("hidden");

                }
            );
        };

        // Handle settings tabs
        self.initNavTabs = function ($elem) {

            var $links = $elem.find('a'),
                $lastSection = null;

            $(window).on(
                'hashchange',
                function () {

                    var hash = window.location.hash.match(new RegExp('tab=([^&]*)')),
                        slug = hash ? hash[1] : $links.first().attr('href').replace('#tab=', ''),
                        $link = $('#pa-tab-link-' + slug);

                    if (!$link.length) {
                        return

                        $link.closest('.pa-settings-tab').addClass('pa-tab-active').siblings().removeClass('pa-tab-active');
                    }
                    $links.removeClass('pa-section-active');
                    $link.addClass('pa-section-active');

                    // Hide the last active section
                    if ($lastSection) {
                        $lastSection.hide();
                    }

                    var $section = $('#pa-section-' + slug);
                    $section.css(
                        {
                            display: 'block'
                        }
                    );

                    $lastSection = $section;

                }
            ).trigger('hashchange');

        };

        self.handleRollBack = function () {

            // Rollback button
            $('.pa-rollback-button').on(
                'click',
                function (event) {

                    event.preventDefault();

                    var $this = $(this),
                        href = $this.attr('href');

                    if (!href) {
                        return;
                    }

                    // Show PAPRO stable version if PAPRO Rollback is clicked
                    var isPAPRO = '';
                    if (-1 !== href.indexOf('papro_rollback')) {
                        isPAPRO = 'papro_';
                    }

                    var premiumRollBackConfirm = premiumAddonsSettings.premiumRollBackConfirm;

                    var dialogsManager = new DialogsManager.Instance();

                    dialogsManager.createWidget(
                        'confirm',
                        {
                            headerMessage: premiumRollBackConfirm.i18n.rollback_to_previous_version,
                            message: premiumRollBackConfirm['i18n'][isPAPRO + 'rollback_confirm'],
                            strings: {
                                cancel: premiumRollBackConfirm.i18n.cancel,
                                confirm: premiumRollBackConfirm.i18n.yes,
                            },
                            onConfirm: function () {

                                $this.addClass('loading');

                                location.href = $this.attr('href');

                            }
                        }
                    ).show();
                }
            );

        };

        self.handlePaproActions = function () {

            // Trigger SWAL for PRO elements
            $(".pro-slider").on(
                'click',
                function () {

                    var redirectionLink = " https://premiumaddons.com/pro/?utm_source=wp-menu&utm_medium=wp-dash&utm_campaign=get-pro&utm_term=";

                    Swal.fire(
                        {
                            title: '<span class="pa-swal-head">Get PRO Widgets & Addons<span>',
                            html: 'Supercharge your Elementor with PRO widgets and addons that you won’t find anywhere else.',
                            type: 'warning',
                            showCloseButton: true,
                            showCancelButton: true,
                            cancelButtonText: "More Info",
                            focusConfirm: true,
                            customClass: 'pa-swal',
                        }
                    ).then(
                        function (res) {
                            // Handle More Info button
                            if (res.dismiss === 'cancel') {
                                window.open(redirectionLink + settings.theme, '_blank');
                            }

                        }
                    );
                }
            );

            // Trigger SWAL for White Labeling
            $(".premium-white-label-form.pro-inactive").on(
                'submit',
                function (e) {

                    e.preventDefault();

                    var redirectionLink = " https://premiumaddons.com/pro/?utm_source=wp-menu&utm_medium=wp-dash&utm_campaign=get-pro&utm_term=";

                    Swal.fire(
                        {
                            title: '<span class="pa-swal-head">Enable White Labeling Options<span>',
                            html: 'Premium Addons can be completely re-branded with your own brand name and author details. Your clients will never know what tools you are using to build their website and will think that this is your own tool set. White-labeling works as long as your license is active.',
                            type: 'warning',
                            showCloseButton: true,
                            showCancelButton: true,
                            cancelButtonText: "More Info",
                            focusConfirm: true
                        }
                    ).then(
                        function (res) {
                            // Handle More Info button
                            if (res.dismiss === 'cancel') {
                                window.open(redirectionLink + settings.theme, '_blank');
                            }

                        }
                    );
                }
            );

        };

    };

    var instance = new PremiumAddonsNavigation();

    instance.init();

})(jQuery);
