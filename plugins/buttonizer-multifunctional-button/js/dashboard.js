
var buttonizer = {
    settings: {
        wpButtonSaveKey: 'buttonizer_buttons',
        wpCategorySaveKey: 'buttonizer_page_categories',
        windowNumber: 0,
        hasChanges: false,
        clickedButton: false
    },

    setTimouts: {
        a: function() {
            return setTimeout(function() { }, 0)
        },
        b: function() {
            return setTimeout(function() { }, 0)
        },
    },

    init: function() {
        if(document.location.href.indexOf('page=Buttonizer') === -1) {
            return;
        }

        buttonizer.overwriteFavIcon();
        buttonizer.faInit();
        buttonizer.initButtons();
        buttonizer.initIntro();

        // WP color picker
        jQuery('#button_unpushed').wpColorPicker();
        jQuery('#button_pushed').wpColorPicker();
        jQuery('#icon_color').wpColorPicker();
        jQuery('.icon_label_color').wpColorPicker();

        jQuery(".savebutton").each(function() {
            jQuery(this).click(buttonizer.saveAnimation);
        });

        jQuery("input, textarea, select").change(function() {
            buttonizer.settings.hasChanges = true;
        });

        setTimeout(function() {
            // Init preview button
            jQuery('.button-preview').css({
                'background-color': jQuery("#button_unpushed").val()
            });

            jQuery(".button-preview").hover(function() {
                jQuery(this).css({
                    'background-color': jQuery("#button_pushed").val()
                });
            }, function() {
                jQuery(this).css({
                    'background-color': jQuery("#button_unpushed").val()
                });
            });
        }, 500);

        jQuery(".vertical-tabs .icon-or-image").each(function() {
            buttonizer.iconImageHandler(jQuery(this));
        });

        // Remove the spam from other plugins
        setTimeout(function() {
            var foundOurContent = false;
            if(jQuery(".buttonizerWarning").length > 0) {
                foundOurContent = true;
                var buttonizerData = jQuery(".buttonizerWarning").html();
                var buttonizerStyle = jQuery(".buttonizerWarning").attr('class');
            }

            jQuery("#wordpress-plugin-message-spam").remove();

            if(foundOurContent) {
                jQuery(".nav-tab-wrapper").after('<div class="'+ buttonizerStyle +'">'+ buttonizerData +'</div>');
            }
        }, 1000);

        // Hash
        if(document.location.hash != "") {
            if(document.location.hash != "#buttonizer-tour") {
                var tabKey = document.location.hash;
                buttonizer.tabNavigate(tabKey.replace("#tab_", ""));
            }
        }

        jQuery(".buttonizer-click-to-pro").each(function() {
            jQuery(this).click(function(e) {
                e.preventDefault();
                buttonizer.proInfoWindow();
            });
        });

        buttonizer.loadPageCategorie();

        if(document.getElementById("button-rows") != null) {
            buttonizer.addSortable();
        }
    },

    proInfoWindow: function() {
        buttonizer.createWindow({
            title: 'You like Buttonizer?',
            text: '<p>Are you ready to try Buttonizer PRO?</p><p><b class="color-buttonizer">What do you get?</b></p>'+

            '<div class="color-buttonizer-blue buttonizer-pro-checklist"><i class="fa fa-check"></i> Show on opening hours</div>' +
            '<div class="color-buttonizer-orange buttonizer-pro-checklist"><i class="fa fa-check"></i> Show buttons on specific pages: Use page rules</div>' +
            '<div class="color-buttonizer-blue buttonizer-pro-checklist"><i class="fa fa-check"></i> Unlimited page rules</div>' +
            '<div class="color-buttonizer-orange buttonizer-pro-checklist"><i class="fa fa-check"></i> Add custom image or button-icon</div>' +
            '<div class="color-buttonizer-blue buttonizer-pro-checklist"><i class="fa fa-check"></i> Exit intend</div>' +
            '<div class="color-buttonizer-orange buttonizer-pro-checklist"><i class="fa fa-check"></i> Show on scroll</div>' +
            '<div class="color-buttonizer-blue buttonizer-pro-checklist"><i class="fa fa-check"></i> Show on timeout</div>' +
            '<div class="color-buttonizer-orange buttonizer-pro-checklist"><i class="fa fa-check"></i> Custom class names</div>' +
            '<div class="color-buttonizer-blue buttonizer-pro-checklist"><i class="fa fa-check"></i> Change the label background and color</div>' +
            '<br />' +
            '<p class="color-buttonizer text-right"><b>Do you <span><i class="fa fa-heart"></i></span> these features?</b></p>',
            confirmText: 'Upgrade',
            onConfirm: function() {
                document.location.href = 'admin.php?page=Buttonizer-pricing';
            }
        });
    },

    overwriteFavIcon: function() {
        if(typeof faviconUrl != 'undefined') {
            (function () {
                var link = document.querySelector("link[rel*='icon']") || document.createElement('link');
                link.type = 'image/x-icon';
                link.rel = 'shortcut icon';
                link.href = faviconUrl;
                document.getElementsByTagName('head')[0].appendChild(link);
            })();
        }
    },

    tabNavigate: function(key) {
        jQuery(".vertical-tabs .tab-container").each(function() {
            if(jQuery(this).attr('id') == "tab_container_" + key) {
                jQuery(this).show();
            }else{
                jQuery(this).hide();
            }
        });

        jQuery(".vertical-tabs .tabs a").each(function() {
            if(jQuery(this).attr('id') == "tab_container_" + key) {
                jQuery(this).addClass('nav-tab-active');
            }else{
                jQuery(this).removeClass('nav-tab-active');
            }
        });

        // jQuery("type[name=_wp_http_referer]").val();
    },

    iconImageHandler: function(currentContainer) {
        var type = currentContainer.data("type");

        // Init
        if(currentContainer.find(".image-background-checkbox").is(":checked")) {
            currentContainer.find('.button-preview').css({
                'background-image': "url('"+ currentContainer.find(".field_image_data").val() +"')",
                'background-size': 'cover',
                'background-position': 'center center'
            });
            currentContainer.find('.button-preview img').hide();
        }

        // Chooser
        currentContainer.find(".placeholder-choose a").click(function() {
            if(type == 'image') {
                type = 'icon';

                currentContainer.find(".image-placeholder").hide();
                currentContainer.find(".icon-placeholder").show();

                currentContainer.find("input").val("");
            }else {
                type = 'image';

                currentContainer.find(".image-placeholder").show();
                currentContainer.find(".icon-placeholder").hide();
            }
        });

        // Insert image and icon type
        currentContainer.find(".image-uploader-button").click(function(e) {
            e.preventDefault();

            var custom_uploader = wp.media({
                title: 'Choose button icon',
                multiple: false
            })
            .on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                currentContainer.find('.button-preview img').attr( 'src', attachment.url).show();
                currentContainer.find(".button-preview").show();

                currentContainer.find('.field_image_data').val(attachment.url);

                if(currentContainer.find(".image-background-checkbox").is(":checked")) {
                    currentContainer.find('.button-preview').css({
                        'background-image': "url('"+ attachment.url +"')",
                        'background-size': 'cover',
                        'background-position': 'center center'
                    });
                    currentContainer.find('.button-preview img').hide();
                }
            })
            .open();
        });

        // Insert image and icon type
        currentContainer.find(".image-background-checkbox").click(function(e) {
            var attachment = currentContainer.find(".field_image_data").val();

            console.log(attachment);

            if(!jQuery(this).is(":checked")) {
                currentContainer.find('img').attr('src', attachment).show();

                currentContainer.find('.button-preview').css({
                    'background-image': "",
                    'background-size': 'cover'
                });
            }else{
                currentContainer.find('img').hide();
                currentContainer.find('.button-preview').css({
                    'background-image': "url('"+ attachment +"')",
                    'background-size': 'cover',
                    'background-position': 'center center'
                });
            }
        });
    },

    colorPaletHandler: function(colorPalet) {
        var buttonId = colorPalet.attr("data-btnid");
        var customColors = false;
        var currentColors = {
            default: buttonizer.rgb2hex(colorPalet.find(".color.default").css('background-color')),
            pushed: buttonizer.rgb2hex(colorPalet.find(".color.pushed").css('background-color')),
            icon: buttonizer.rgb2hex(colorPalet.find(".color.icon").css('background-color'))
        };

        jQuery("#btn_row_"+ buttonId +" .button-preview").css({ 'background-color': currentColors.default });

        colorPalet.click(function() {
            var randomText = (buttonizer.settings.windowNumber + 1);

            buttonizer.createWindow({
                title: 'Choose colors',
                text:
                    '<p>Choose the colors here of your button. You can give every button different colors.</p>' +
                    '<table width="100%" class="color-chooser-table">' +
                        '<tr><td width="150">' +
                            '<b>Button color</b>' +
                            '</td><td>' +
                            '<input type="text" class="colorpalet-initializer" value="'+ currentColors.default +'" id="color_default_'+ randomText +'" />' +
                        '</td></tr>' +

                        '<tr><td>' +
                            '<b>Pushed/hover color</b>' +
                            '</td><td>' +
                            '<input type="text" class="colorpalet-initializer" value="'+ currentColors.pushed +'" id="color_pushed_'+ randomText +'" />' +
                        '</td></tr>' +

                        '<tr><td>' +
                            '<b>Icon color</b>' +
                            '</td><td>' +
                            '<input type="text" class="colorpalet-initializer" value="'+ currentColors.icon +'" id="color_icon_'+ randomText +'" />' +
                        '</td></tr>' +
                    '</table>',

                confirmText: 'Choose',

                afterInit: function() {
                    jQuery(".colorpalet-initializer").wpColorPicker();
                },

                onConfirm: function() {
                    currentColors.default = jQuery("#color_default_" + randomText).val();
                    currentColors.pushed = jQuery("#color_pushed_" + randomText).val();
                    currentColors.icon = jQuery("#color_icon_" + randomText).val();
                    jQuery("#btn_row_"+ buttonId +" .button-preview").css({ 'background-color': currentColors.default });
                    customColors = true;

                    jQuery("#reset_colors_" + buttonId).show();
                    updatePalet();
                }
            });
        });

        jQuery("#reset_colors_" + buttonId).click(function() {
            buttonizer.createWindow({
                title: 'Back to default colors',
                text: '<p>The button colors will reset to the choosen default button colors (from the general settings).</p>' +
                '<p>Are you sure you want to get the default colors for this button?</p>',

                canCancel: true,
                cancelText: 'Cancel',
                confirmText: '<i class="fa fa-thumbs-o-up"></i>&nbsp; Yes, I am',

                onConfirm: function() {
                    customColors = false;
                    currentColors.default = colorPalet.find(".color.default").attr("data-default");
                    currentColors.pushed = colorPalet.find(".color.pushed").attr("data-default");
                    currentColors.icon = colorPalet.find(".color.icon").attr("data-default");
                    updatePalet();
                    jQuery("#reset_colors_" + buttonId).hide();
                    jQuery("#btn_row_"+ buttonId +" .button-preview").css({ 'background-color': currentColors.default });
                }
            });
        });

        function updatePalet() {
            colorPalet.find(".color.default").css({ 'background-color': currentColors.default});
            colorPalet.find(".color.pushed").css({ 'background-color': currentColors.pushed});
            colorPalet.find(".color.icon").css({ 'background-color': currentColors.icon});
            colorPalet.find(".text").html(customColors ? 'Custom colors' : 'Default colors');

            colorPalet.find("input.custom").val(customColors ? '1' : '0').change();
            colorPalet.find("input.default").val(currentColors.default);
            colorPalet.find("input.pushed").val(currentColors.pushed);
            colorPalet.find("input.icon").val(currentColors.icon);
        }
    },

    rgb2hex: function(rgb) {
        if(rgb == null || typeof rgb == "null") {
            return '#eeeeee';
        }

        rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        function hex(x) {
            return ("0" + parseInt(x).toString(16)).slice(-2);
        }

        if(rgb == null || typeof rgb == "null") {
            return '#eeeeee';
        }

        return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
    },

    loadPageCategorie: function() {

    },

    addSortable: function() {
        var sortable = new Sortable(document.getElementById("button-rows"), {
        	group: "button-rows",
        	sort: true,
        	animation: 150,
        	scroll: true,
        	scrollSensitivity: 30,
        	scrollSpeed: 10,
            handle: ".drag-handle",

        	onSort: function (evt) {
                jQuery("#" + evt.clone.children[0].id + " .row-info").css({
                    opacity: .45,
                }).delay(500).animate({
                    opacity: 1,
                }, 500);
        	}
        });
    },

    saveAnimation: function() {
        jQuery(".savebutton").each(function() {
            jQuery(this).html('<i class="fa fa-cog fa-spin"></i> <span>Saving</span>');
        });
        buttonizer.settings.clickedButton = true;
        jQuery('form#buttonizer').submit();
    },

    /*
     * Buttons
     */
    initButtons: function() {
        jQuery(".button-row .savebutton").each(function() {
            jQuery(this).click(buttonizer.saveAnimation);
        });

        jQuery(".button-row").each(function() {
            if(jQuery(this).attr("button-id") == '-1') {
                return;
            }

            var rowObj = jQuery(this);
            var rowId = jQuery(this).attr("button-id");
            var buttonTitle = rowObj.find(".row-info span .row-title");
            var buttonEdit = rowObj.find(".row-info .pencil-edit");
            var buttonTitleField = rowObj.find(".button_title");
            var doubleClick = setTimeout(function() {}, 100);
            var isCategory = rowObj.hasClass('is_category');
            var clicked = 0;

            buttonEdit.click(function(e) {
                e.preventDefault();
                buttonTitle.dblclick();
            });

            buttonTitle.html(buttonTitleField.val());

            rowObj.find(".row-info").click(function(e) {
                if(
                    jQuery(e.target).hasClass('fa-desktop') ||
                    jQuery(e.target).hasClass('fa-mobile') ||
                    jQuery(e.target).hasClass('fa-pencil') ||
                    jQuery(e.target).hasClass('mobiledesktop') ||
                    jQuery(e.target).hasClass('is-live-button') ||
                    jQuery(e.target).hasClass('must-save-button')
                ) {
                    return;
                }

                clicked = 0;
                clearInterval(doubleClick);
                doubleClick = setTimeout(function() {
                    if(clicked == 0) {
                        if(rowObj.hasClass("opened")) {
                            rowObj.removeClass("opened");
                        }else{
                            rowObj.addClass("opened");
                        }
                    }
                    clicked = 0;
                }, 100);
            });

            buttonTitle.dblclick(function() {
                buttonizer.updateTitle(buttonTitle, buttonTitleField, false);

                clicked++;
            });

            rowObj.find(".button_showonphone").change(function(e) {
                var isSelected = jQuery(this).is(":checked");

                if(!isSelected) {
                    jQuery("#btn_row_" + rowId + " .row-info .mobile-button").addClass("selected");
                }else {
                    jQuery("#btn_row_" + rowId + " .row-info .mobile-button").removeClass("selected");
                }
            });

            rowObj.find('.button_title').change(function() {
                newTitle = jQuery(this).val();

                if(buttonizer.checkTitleDuplicate(newTitle, rowId)) {
                    buttonizer.updateTitle(buttonTitle, buttonTitleField, true, newTitle);
                    return;
                }

                buttonTitle.html(newTitle);
            });

            rowObj.find(".button_showondesktop").change(function(e) {
                var isSelected = jQuery(this).is(":checked");

                if(!isSelected) {
                    jQuery("#btn_row_" + rowId + " .row-info .desktop-button").addClass("selected");
                }else {
                    jQuery("#btn_row_" + rowId + " .row-info .desktop-button").removeClass("selected");
                }
            });

            jQuery("#button_"+ rowId +"_icon").change(function(e) {
                rowObj.find(".button-icon").attr('class', "fa "+ jQuery(this).val() +" button-icon");
            });

            jQuery("#btn_row_" + rowId + " input").change(function() {
                jQuery("#btn_row_" + rowId + " .row-info .is-live-button").hide();
                jQuery("#btn_row_" + rowId + " .row-info .must-save-button").show();
            });

            if(isCategory) {

            }else{
                // Image icon handler
                buttonizer.iconImageHandler(rowObj.find(".icon-or-image"));
                buttonizer.colorPaletHandler(rowObj.find(".button-color-palet"));

                buttonizer.buttonTypeHandler(rowObj);
            }
        });
    },

    updateTitle: function(buttonTitle, buttonTitleField, isDuplicate, text) {
        if(!text) {
            var text = '';
        }

        if(!isDuplicate) {
            var newTitle = prompt("You can change the title of the button.\n\nNote: The visitor won't see this text - for internal use only:", buttonTitleField.val());
        }else{
            var newTitle = prompt("You can change the title of the button.\n\nNote: The visitor won't see this text - for internal use only:\n\nWARNING: This name is already in use. Please change the name of the button. Duplicates are not allowed due Google Analytics event names.", text);
        }

        if(!newTitle || newTitle == "") {
            if(isDuplicate) {
                buttonTitleField.val(buttonTitle.html());
            }
            return;
        }

        if(buttonizer.checkTitleDuplicate(newTitle)) {
            buttonizer.updateTitle(buttonTitle, buttonTitleField, true, newTitle);
            return;
        }

        buttonTitle.html(newTitle);
        buttonTitleField.val(newTitle);
    },

    checkTitleDuplicate: function(text, allowRowId) {
        isDuplicate = false;
        if(!allowRowId) {
            var allowRowId = -1;
        }

        jQuery(".button-row").each(function() {
            if(jQuery(this).find('.button_title').val() == text && jQuery(this).attr("button-id") != allowRowId) {
                isDuplicate = true;
            }
        });

        return isDuplicate;
    },

    removeRow: function(id) {
        var buttonName = jQuery("#btn_row_" +id + " .row-info span .row-title").html();
        var wasCategory = jQuery("#btn_row_" + id).hasClass('is_category');

        if(confirm("Are you sure you want to remove the row with the text: '"+ buttonName +"'?\n\nThis is not recoverable.")) {
            jQuery("#btn_row_" +id).remove();

            if(jQuery(".button-row").length > 1) {
                jQuery(".buttonizer-no-buttons").hide();
            }else{
                jQuery(".buttonizer-no-buttons").show();
            }
        }
    },

    toggleMobile: function(id) {
        var isSelected = jQuery("#button_"+ id +"_show_on_phone").is(":checked");

        if(!isSelected) {
            jQuery("#btn_row_" + id + " .row-info .mobile-button").removeClass("selected");
            jQuery("#button_"+ id +"_show_on_phone").click();
        }else {
            jQuery("#btn_row_" + id + " .row-info .mobile-button").addClass("selected");
            jQuery("#button_"+ id +"_show_on_phone").click();
        }

        jQuery("#btn_row_" + rowId + " .row-info .is-live-button").hide();
        jQuery("#btn_row_" + rowId + " .row-info .must-save-button").show();
    },

    toggleDesktop: function(id) {
        var isSelected = jQuery("#button_"+ id +"_show_on_desktop").is(":checked");

        if(!isSelected) {
            jQuery("#btn_row_" + id + " .row-info .desktop-button").removeClass("selected");
            jQuery("#button_"+ id +"_show_on_desktop").click();
        }else {
            jQuery("#btn_row_" + id + " .row-info .desktop-button").addClass("selected");
            jQuery("#button_"+ id +"_show_on_desktop").click();
        }

        jQuery("#btn_row_" + rowId + " .row-info .is-live-button").hide();
        jQuery("#btn_row_" + rowId + " .row-info .must-save-button").show();
    },

    addRow: function(copyButton) {
        if(jQuery("#new-button").hasClass('disabled')) {
            return;
        }
        buttonizer.settings.hasChanges = true;

        jQuery(".buttonizer-no-buttons").hide();

        var isCategory = jQuery(".button-row[button-id=-1]").hasClass("is_category");

        jQuery("#new-button").attr('disabled', 'disabled').addClass('disabled');

        var defaultRow = "";
        if(!copyButton) {
            defaultRow = jQuery(".button-row[button-id=-1]").html();
        }else{
            defaultRow = jQuery(".button-row[button-id="+ copyButton +"]").html();
        }
        var numberOfButtons = jQuery(".button-row").length - (isCategory ? 1 : 0);

        numberOfButtons = Math.floor(Date.now() / 1000);
        numberOfButtons = numberOfButtons + "-" + Math.floor((Math.random() * 999) + 100);
        numberOfButtons = numberOfButtons.replace('-', '');

        var newData = defaultRow.replace('/'+ copyButton +'/g', numberOfButtons);
        newData = newData.replace(copyButton, numberOfButtons);

        jQuery("#button-rows, #page-categories").append((!isCategory ? '<li>' : '') + '<div class="button-row ' + (isCategory ? 'is_category' : '') + '" id="btn_row_'+ numberOfButtons +'" button-id="'+ numberOfButtons +'">' + newData + '</div>' + (!isCategory ? '</li>' : ''));

        clearInterval(buttonizer.setTimouts.a);
        clearInterval(buttonizer.setTimouts.b);

        buttonizer.setTimouts.a = setTimeout(function() {
            jQuery(".button-row *").unbind("click");
            jQuery(".button-row").removeClass("opened");

            jQuery("#btn_row_"+ numberOfButtons +" >input[type=checkbox]").val(numberOfButtons);

            jQuery(".fontawesome-searcher-clicker").remove();
            jQuery("#btn_row_"+ numberOfButtons +" .button_title").val("New button " + numberOfButtons);

            if(isCategory) {
                jQuery("#btn_row_"+ numberOfButtons +" input").each(function() {
                    jQuery(this).attr("name", buttonizer.settings.wpCategorySaveKey + "[category_"+ numberOfButtons +"][]");
                });

                jQuery("#btn_row_"+ numberOfButtons + " select").attr("name", buttonizer.settings.wpCategorySaveKey + "[category_"+ numberOfButtons +"_type]");
            }
        }, 100);


        buttonizer.setTimouts.b = setTimeout(function() {
            buttonizer.initButtons();
            buttonizer.updateRowInputNames(numberOfButtons);
            jQuery("#btn_row_"+ numberOfButtons).addClass('opened');
            jQuery("#btn_row_"+ numberOfButtons + " input.button_title").focus();
            buttonizer.faInit();
            buttonizer.loadPageCategorie();
            jQuery("#new-button").removeAttr('disabled').removeClass('disabled');

            jQuery("#btn_row_"+ numberOfButtons + " .savebutton").show();
        }, 400);
    },

    copyRow: function(id) {
        this.addRow(id);
    },

    updateRowInputNames: function(buttonId) {
        // Textfield
        jQuery("#btn_row_"+ buttonId + " .button_textfield")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_text]")
            .attr("id", "button_"+ buttonId +"_text").val("");

        // Iconfield
        jQuery("#btn_row_"+ buttonId + " .icon-placeholder select").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_icon]");

        // Iconfield
        jQuery("#btn_row_"+ buttonId + " .image-placeholder input").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_image]");

        // Button title
        jQuery("#btn_row_"+ buttonId + " .button_title").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_title]");

        // Button show on page category
        jQuery("#btn_row_"+ buttonId + " #button_category").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_show_on_pages]");

        // Color palets
        jQuery("#btn_row_"+ buttonId + " input.custom").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_using_custom_colors]");

        jQuery("#btn_row_"+ buttonId + " input.default").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_colors_button]");
        jQuery("#btn_row_"+ buttonId + " input.pushed").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_colors_pushed]");
        jQuery("#btn_row_"+ buttonId + " input.icon").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_colors_icon]");

        // Button action
        jQuery("#btn_row_"+ buttonId + " .button_action").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_action]");

        // Button value
        jQuery("#btn_row_"+ buttonId + " .button_input").attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_url]");


        // Show on phone
        jQuery("#btn_row_"+ buttonId + " .button_showonphone")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_show_on_phone]")
            .attr("id", "button_"+ buttonId +"_show_on_phone");

        jQuery("label[for='button_"+ buttonId +"_show_on_phone']").attr("for", "button_"+ buttonId +"_show_on_phone");

        // Show on desktop
        jQuery("#btn_row_"+ buttonId + " .button_showondesktop")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_show_on_desktop]")
            .attr("id", "button_"+ buttonId +"_show_on_desktop");

        jQuery("label[for='button_"+ buttonId +"_show_on_desktop']").attr("for", "button_"+ buttonId +"_show_on_desktop");

        // Url
        jQuery("#btn_row_"+ buttonId + " .button_isurl")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_url]")
            .attr("id", "button_"+ buttonId +"_url");

        jQuery("label[for='button_"+ buttonId +"_url']").attr("for", "button_"+ buttonId +"_url");

        // Is phone buttonId
        jQuery("#btn_row_"+ buttonId + " .button_isphonenumber")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_is_phonenumber]")
            .attr("id", "button_"+ buttonId +"_is_phonenumber");

        jQuery("label[for='button_"+ buttonId +"_is_phonenumber']").attr("for", "button_"+ buttonId +"_is_phonenumber");

        // New tab
        jQuery("#btn_row_"+ buttonId + " .button_isnewtab")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_url_newtab]")
            .attr("id", "button_"+ buttonId +"_url_newtab");

        jQuery("label[for='button_"+ buttonId +"_url_newtab']").attr("for", "button_"+ buttonId +"_url_newtab");


        // Show only on opening hours
        jQuery("#btn_row_"+ buttonId + " .button_showwhenopened")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_show_when_opened]")
            .attr("id", "button_"+ buttonId +"_show_when_opened");

        jQuery("label[for='button_"+ buttonId +"_show_when_opened']").attr("for", "button_"+ buttonId +"_show_when_opened");

        // Show only on opening hours
        jQuery("#btn_row_"+ buttonId + " .button_showlabelonhover")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_show_label_on_hover]")
            .attr("id", "button_"+ buttonId +"_show_label_on_hover");

        jQuery("label[for='button_"+ buttonId +"_show_label_on_hover']").attr("for", "button_"+ buttonId +"_show_label_on_hover");

        // Custom class
        jQuery("#btn_row_"+ buttonId + " .button_custom_class")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_custom_class]")
            .attr("id", "button_"+ buttonId +"_custom_class");

        jQuery("label[for='button_"+ buttonId +"_custom_class']").attr("for", "button_"+ buttonId +"_custom_class");

        // Change background
        jQuery("#btn_row_"+ buttonId + " .image-background-checkbox")
            .attr("name", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_image_background]")
            .attr("id", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_image_background]");

        jQuery("#btn_row_"+ buttonId + " .icon-or-image label").attr("for", buttonizer.settings.wpButtonSaveKey + "[button_"+ buttonId +"_image_background]")
        /*
         * Link fixes
         */
        // Delete button link
        jQuery("#btn_row_"+ buttonId + " .delete-button").attr("href", "javascript:buttonizer.removeRow("+ buttonId +")");

        jQuery("#btn_row_"+ buttonId + " .copy-button").attr("href", "javascript:buttonizer.copyRow("+ buttonId +")");

        // Show on mobile button link
        jQuery("#btn_row_"+ buttonId + " .mobiledesktop.mobile-button").attr("href", "javascript:buttonizer.toggleMobile("+ buttonId +")");

        // Show on desktop button link
        jQuery("#btn_row_"+ buttonId + " .mobiledesktop.desktop-button").attr("href", "javascript:buttonizer.toggleDesktop("+ buttonId +")");
    },

    /* Button type handler */
    buttonTypeHandler: function(rowObj) {
        var currentButtonType = rowObj.find(".button_action");
        var currentButtonInput = rowObj.find(".button_input");
        var currentSocialInput = rowObj.find(".social_input");
        var newTab = rowObj.find(".setting-new-tab");

        var isInitializing = true;

        var waitUntilTriggering = setTimeout(function() {});

        // The button type
        currentButtonType.change(function() {
            isInitializing = true;
            checkInput();
        });

        currentButtonInput.keyup(function() {
            clearInterval(waitUntilTriggering);

            waitUntilTriggering = setTimeout(function(){
                isInitializing = true;
                checkInput();
            }, 250);
        });

        currentButtonInput.change(checkInput);
        currentSocialInput.change(checkInput);

        //
        // Checkbox validation



        function checkInput() {
            var inputError = false;
            var currentAction = currentButtonType.val();
            var currentValueText = currentButtonInput.val();

            if(currentAction == 'backtotop') {
                currentButtonInput.removeAttr("hidden", "hidden");
                currentButtonInput.attr("disabled", "disabled");
                currentSocialInput.hide();
            }else if(currentAction == 'gobackpage') {
                currentButtonInput.attr("hidden", "hidden");
                currentButtonInput.attr("disabled", "disabled");
                currentSocialInput.hide();

            }else if(currentAction == 'socialsharing'){
                currentButtonInput.attr("hidden", "hidden");
                currentSocialInput.show();

            } else {
                currentButtonInput.removeAttr("hidden", "hidden");
                currentButtonInput.removeAttr("disabled", "disabled");
                currentSocialInput.hide();

}

            // if(!isInitializing && currentValueText == '') {
            //     isInitializing = false;
            //     rowObj.find(".input_error").html('Hello there... You left the input empty here... Do you want to fill him up? It\'s up to you.').show();
            //     return;
            // }

            if(currentButtonType.val() == 'url') {
                newTab.show();
            }else{
                newTab.hide();
            }

            if(currentAction === "socialsharing"){
                rowObj.find(".input_error").hide();
                rowObj.find(".extra_info").hide();
                return;
            }

            if(!isInitializing && currentAction == 'url' && currentValueText == '#') {
                inputError = true;
                rowObj.find(".input_error").html('<p>You only have #... That doesn\'t open anything...</p>');
            }else if(!isInitializing && currentAction == 'url' && !buttonizer.buttonTypeInputController(currentValueText, 'url')) {
                inputError = true;
                rowObj.find(".input_error").html('<p>This looks like an invalid URL. The button may not work as expected.</p><p>&nbsp;</p><p>Do you miss <b>http://</b> or <b>https://</b>? A space somewhere on the wrong place?</p>');
            }
1
            if((currentAction == 'phone' || currentAction == 'whatsapp') && !/^(?=.*\d)[\d ]+$/.test(currentValueText)) {
                inputError = true;
                rowObj.find(".input_error").html('Invalid phone number. Please use only the number format. Omit any zeroes, brackets or dashes when adding the phone number in international format.');
            }

            if(currentAction == 'mail' && !buttonizer.buttonTypeInputController(currentValueText, 'mail')) {
                inputError = true;
                rowObj.find(".input_error").html('<p>This looks like an invalid mail address. Make sure the mail address looks like <b><i>user</i>@domain.com</b></p>');
            }

            
/* Premium Code Stripped by Freemius */



            if(!isInitializing && currentValueText == '') {
                rowObj.find(".input_error").html('Uhm... You forgot something to fill in...').show();
                return;
            }

            // Stay here
            if(!isInitializing) {
                if(currentAction != 'phone' && currentAction != 'whatsapp' && /^(?=.*\d)[\d ]+$/.test(currentValueText)) {
                    buttonizer.createWindow({
                        title: 'Uhm? Shall I call you?',
                        text:
                            '<p>It\'s looks like you are having a phone number as value. Do you want to let this button behave as a call button?</p>' +
                            '<p>If it\'s not a phone number, you can click \'No thanks\'. If you click \'Yes please\' I will change it for you.</p>',

                        confirmText: 'Yes please',
                        canCancel: true,
                        cancelText: 'No thanks',

                        onConfirm: function() {
                            currentButtonType.val('phone');
                            rowObj.find(".input_error").hide();
                        }
                    });
                }

                if(currentAction != 'url' && buttonizer.buttonTypeInputController(currentValueText, 'url')) {
                    buttonizer.createWindow({
                        title: 'Uhm? WWW.?',
                        text:
                            '<p>It\'s looks like you are having a website URL as value. Do you want to let this button behave as a link?</p>' +
                            '<p>If you just about to change that, you can click \'No thanks\'. If you click \'Yes please\' I will change it for you.</p>',

                        confirmText: 'Yes please',
                        canCancel: true,
                        cancelText: 'No thanks',

                        onConfirm: function() {
                            currentButtonType.val('url');
                            rowObj.find(".input_error").hide();
                        }
                    });
                }

                if(currentAction != 'mail' && buttonizer.buttonTypeInputController(currentValueText, 'mail')) {
                    buttonizer.createWindow({
                        title: 'Uhm? That\'s a mail address?',
                        text:
                            '<p>It\'s looks like you are having a mail address as value. Do you want to change the button action to open the mail program?</p>' +
                            '<p>If you just about to change that, you can click \'No thanks\'. If you click \'Yes please\' I will change it for you.</p>',

                        confirmText: 'Yes please',
                        canCancel: true,
                        cancelText: 'No thanks',

                        onConfirm: function() {
                            currentButtonType.val('mail');
                            currentButtonInput.val(currentValueText.replace("mailto:", ""));
                            rowObj.find(".input_error").hide();
                        }
                    });
                }

                
/* Premium Code Stripped by Freemius */

            }

            if(inputError) {
                rowObj.find(".input_error").show();
            }else{
                rowObj.find(".input_error").hide();
            }

            isInitializing = false;
        };

        // Start
        checkInput();
    },

    buttonTypeInputController: function(inputValue, action) {
        var websiteUrlPattern = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
        var mailUrlPattern = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;

        // URL
        if(action == 'url') {
            if(websiteUrlPattern.test(inputValue) || inputValue.substring(0, 1) == '#' || inputValue.substring(0, 1) == '/') {
                return true;
            } else {
                return false;
            }
        }else

        // E-mail
        if(action == 'mail') {
            if(!mailUrlPattern.test(inputValue) && inputValue.indexOf("mailto:") == -1) {
                return false;
            } else {
                return true;
            }
        }
    },

    createWindow: function(data) {
        this.settings.windowNumber++;
        var currentWindow = this.settings.windowNumber;

        if(!data.title) {
            data.title = '';
        }

        if(!data.text) {
            data.text = '<p>This dialog has not text.</p><p>Weird, isn\'t it?</p>';
        }

        if(!data.confirmText) {
            data.confirmText = 'Close';
        }

        if(!data.canCancel) {
            data.canCancel = false;
        }

        if(!data.cancelText) {
            data.cancelText = 'Cancel';
        }

        if(!data.onConfirm) {
            data.onConfirm = function() {};
        }

        if(!data.onCancel) {
            data.onCancel = function() {};
        }

        if(!data.onClose) {
            data.onClose = function() {};
        }

        if(!data.afterInit) {
            data.afterInit = function() {};
        }

        var windowStyle = '<div class="fs-modal active" id="buttonizer-window-'+ currentWindow +'"><div class="fs-modal-dialog">';

            windowStyle += '<div class="fs-modal-header"><h4>'+ data.title +'</h4><a href="!#" class="fs-close"><i class="dashicons dashicons-no" title="Dismiss"></i></a></div>';

    		windowStyle += '<div class="fs-modal-body"><div class="fs-modal-panel active">';
                windowStyle += data.text;
            windowStyle += '</div></div>';

    		windowStyle += '<div class="fs-modal-footer">' + (data.canCancel ? '<button class="button cancel" tabindex="3">'+ data.cancelText +'</button> ' : '') +' <button class="button button-primary confirm" tabindex="4">'+ data.confirmText +'</button></div>';

        windowStyle += '</div></div>';

        jQuery("body").append(windowStyle);

        jQuery(document).ready(data.afterInit);

        jQuery("#buttonizer-window-"+ currentWindow + " .fs-close, #buttonizer-window-"+ currentWindow + " .fs-modal-footer button").click(function(e) {
            e.preventDefault();
            jQuery("#buttonizer-window-"+ currentWindow).fadeOut();

            if(jQuery(this).hasClass("confirm")) {
                data.onConfirm();
            }

            if(jQuery(this).hasClass("cancel")) {
                data.onCancel();
            }

            data.onClose();
        })
    },

    /*
     * Font awesome searcher
     */
    faSettings: {
        objects: 0,
        currentSelect: {},
        currentObject: {},
        isGenerated: false,
        isOpened: false
    },
    faInit: function() {
        buttonizer.faSettings.isOpened = false;

        if(buttonizer.faSettings.isGenerated == false && typeof fontAwesome != 'undefined') {
            buttonizer.faSettings.isGenerated = true;
            fontAwesome = jQuery.map(fontAwesome, function(value, index) {
                return [index];
            });
        }

        jQuery("body").click(function(e){
            if(!jQuery(e.target).closest('.fontawesome-searcher').length && !jQuery(e.target).closest('.fontawesome-searcher-clicker').length){
                jQuery(".fontawesome-searcher").hide();
                buttonizer.faSettings.isOpened = false;
            }
        });

        var typeTimeOut = setTimeout(function() { buttonizer.faSearchWord(''); }, 300);

        var adminBody = jQuery("#wpbody");

        jQuery(".fontawesome-searcher .fontawesome-searcher-searchbar input").keyup(function() {
            clearInterval(typeTimeOut);

            typeTimeOut = setTimeout(function() { buttonizer.faSearchWord(jQuery(".fontawesome-searcher .fontawesome-searcher-searchbar input").val()); }, 300);
        });

    	jQuery("select.fa-chooser").each(function() {
    		buttonizer.faSettings.objects++;
    		var obj = jQuery(this);

    		obj.hide();
    		obj.parent().append("<a href=\"javascript:void(0)\" class=\"fontawesome-searcher-clicker\" id=\"fa-chooser_"+ buttonizer.faSettings.objects +"\">Icon: "+ obj.val() +" &nbsp; <i class=\"fa "+ obj.val() +"\"></i></a>");

    		var newObject = jQuery("#fa-chooser_" + buttonizer.faSettings.objects);
    		newObject.click(function(e) {
                if(buttonizer.faSettings.isOpened == false || buttonizer.faSettings.currentSelect != obj) {
                    jQuery(".fontawesome-searcher .fontawesome-searcher-searchbar input").focus();
                    buttonizer.faSettings.isOpened = true;

                    // Update process
        			jQuery(".fontawesome-searcher").css({
        				"top": newObject.offset().top - 20 + newObject.height(),
        				"left": newObject.offset().left - adminBody.offset().left,
        				"width": newObject.width()
        			}).show();

                    buttonizer.faSettings.currentSelect = obj;
                    buttonizer.faSettings.currentObject = newObject;
                }else{
                    buttonizer.faSettings.isOpened = false;
                    jQuery(".fontawesome-searcher").hide();
                }

    			e.preventDefault();
    		});
    	});
    },
    faSearchWord: function(word) {
        var foundMatches = [];

        for(var i = 0; i < fontAwesome.length; i++) {
            if(fontAwesome[i].indexOf(word) !== -1) {
                foundMatches.push(fontAwesome[i]);
            }
        };

        if(foundMatches.length == 0) {
            jQuery(".fontawesome-searcher .no-matches-found").show();
            jQuery(".fontawesome-searcher .fontawesome-searcher-icons").hide();
        }else{
            jQuery(".fontawesome-searcher .no-matches-found").hide();
            jQuery(".fontawesome-searcher .fontawesome-searcher-icons").show();

            var text = '<table><tr>';
            for(var b = 0; b < foundMatches.length; b++) {
                if(b % 4 == 0) {
                    text += "</tr><tr>";
                }

                text += '<td><a href="javascript:buttonizer.faChooseIcon(\''+ foundMatches[b] +'\')"><i class="fa '+ foundMatches[b] +'"></i></a></td>';
            };

            text += '</tr>';

            jQuery(".fontawesome-searcher .fontawesome-searcher-icons").html(text);
        }
    },
    faChooseIcon: function(icon) {
        buttonizer.faSettings.currentSelect.val(icon).change();
        buttonizer.faSettings.currentObject.html("Icon: "+ icon +" &nbsp; <i class=\"fa "+ icon +"\"></i>");
        jQuery("#" + buttonizer.faSettings.currentObject.attr("id")).click();
    },

    /* intro */
    initIntro: function() {
        var openedDesign = false;

        if(RegExp("welcome-splash", "gi").test(document.location.href)) {
            buttonizer.createWindow({
                title: 'Welcome to Buttonizer',
                text:
                    '<img src="'+ jQuery(".buttonizer-logo img").attr("src").replace("logo.png", "plugin-icon.png") +'" width="100" align="left" style="margin-right: 20px; margin-bottom: 50px;" />' +
                    '<p>We are pleased to welcome you to Buttonizer!</p>' +
                    '<p>We\'ve created a tour for our new users, would you like to take the tour? We will make your first Buttonizer button there and show some things how it works!</p>' +
                    '<p>Would you like to take the tour?</p>',

                confirmText: 'Yes please <i class="fa fa-chevron-right" style="margin-left: 10px; vertical-align: middle;" aria-hidden="true"></i>',
                canCancel: true,
                cancelText: 'No thanks, I know how it works',

                onConfirm: function() {
                    document.location.href = '?page=Buttonizer&tab=buttonizer_general_settings#buttonizer-tour';
                },

                onCancel: function() {
                    document.location.href = '?page=Buttonizer';
                }
            });
        }

        // Check if we need to send the user to the next page
        if(RegExp("buttonizer_tour_ready=toStep2", "gi").test(document.cookie)) {
            var d = new Date();
            d.setTime(d.getTime() - 10);
            document.cookie = "buttonizer_tour_ready=toStep2;expires="+ d.toUTCString() + ";path=/";

            document.location.href = './admin.php?page=Buttonizer&tab=buttonizer_buttons#buttonizer-tour';
            return;
        }

        jQuery("#take-the-tour").click(function() {
            setTimeout(function() {
                if (RegExp("buttonizer_general_settings", "gi").test(window.location.href)) {
                    buttonizer.initIntro();
                }
            }, 250);
        });

        if (RegExp("buttonizer-tour", "gi").test(window.location.href)) {

            // Page one: General settings
            if(document.location.href.indexOf('buttonizer_general_settings') != -1) {
                var buttonizer_tour_1 = buttonizer.startTour('design');
                buttonizer.tabNavigate('design');

                buttonizer_tour_1.onbeforechange(function(targetElement) {
                    if (buttonizer_tour_1._currentStep == 5 && buttonizer_tour_1._direction == "forward") {
                        buttonizer.tabNavigate('placing');
                        openedDesign = true;
                    }else if(buttonizer_tour_1._currentStep == 4 && buttonizer_tour_1._direction == "backward") {
                        buttonizer.tabNavigate('design');

                        openedDesign = false;
                    }
                }).oncomplete(function() {
                    document.querySelector('.button.savebutton').click();
                }).setOptions({
                    doneLabel: 'Save &rarr;'
                }).start();

                jQuery("#tab_container_placing").click(function() {
                    buttonizer_tour_1.nextStep();
                });

                jQuery('.button.savebutton').click(function() {
                    var d = new Date();
                    d.setTime(d.getTime() + (2*24*60*60*1000));
                    document.cookie = "buttonizer_tour_ready=toStep2;expires="+ d.toUTCString() + ";path=/";
                });
            }

            if(document.location.href.indexOf('buttonizer_buttons') != -1) {
                var buttonizer_tour_2 = buttonizer.startTour('buttons');
                var buttonCreated = true;

                buttonizer_tour_2.onchange(function(targetElement) {
                    if(buttonizer_tour_2._currentStep == 4) {
                        jQuery("#new-button").click();
                    }
                }).setOptions({
                    doneLabel: 'Next',
                    hidePrev: true,
                    hideNext: true,
                }).start();

                // Create button
                var buttonCreated = false;
                jQuery("#new-button").click(function() {
                    if(buttonCreated) {
                        alert("Please finish the tour first.")
                        return;
                    }
                    buttonCreated = true;
                    buttonizer.addRow();

                    setTimeout(function() {
                        var id = jQuery("#button-rows .button-row.opened").attr("id");
                        buttonizer_tour_2.exit(true);

                        var buttonizer_tour_3 = buttonizer.startTour('edit-button', id);

                        buttonizer_tour_3.onchange(function(targetElement) {
                            if(buttonizer_tour_3._currentStep == 1) {
                                // buttonizer_tour_3.nextStep();
                            }
                        }).setOptions({
                            doneLabel: 'Finish tour',
                            hidePrev: true,
                            hideNext: true,
                        }).start();
                    }, 400);
                });
            }
        }
    },

    startTour: function(type, newButtonId) {
        var intro = introJs();

        if(!newButtonId) {
            newButtonId = false;
        }

        intro.setOption('tooltipPosition', 'top');
        intro.setOptions({
            showBullets: false,
            exitOnOverlayClick: false,
            skipLabel: 'Exit tour'
        });

        if(type == 'design') {
            intro.setOptions({
                steps: [
                {
                    intro: "Let's start the tour through Buttonizer. You can skip this at any moment."
                },
                {
                    element: document.querySelector('.intro-styling'),
                    intro: "Set the default colors of The Buttonizer. We recommend using the colors of your corporate identity.",
                    position: 'top'
                },
                {
                    element: document.querySelector('.intro-icon'),
                    intro: "Select the main icon. This icon will appear when you have multiple floating action buttons on one page (So you can create a Floating Menu).<br><br>When you have PRO featues, you will be able to choose a custom image.",
                    position: 'top'
                },
                {
                    element: document.querySelector('.intro-label'),
                    intro: "Optional: Add text If you want to have a label next to your button to explain the button action like 'Click here' or 'Contact options'.",
                    position: 'top'
                },
                {
                    element: document.querySelector('#tab_container_placing'),
                    intro: "Click here to go to the position and animation page",
                    position: 'right'
                },
                {
                    element: document.querySelector('.intro-position'),
                    intro: "Here you can change the placing of the Buttonizer. 5% means 5% of the screen. The default setting is bottom 5% and right 5%. It will place itself on the right corner of your screen (mobile & desktop).",
                    position: 'top'
                },
                {
                    element: document.querySelector('.intro-animaton'),
                    intro: "The Button animation option is only relevant if you are going to add multiple floating action buttons on one page (create a floating menu). This option let's you choose how the different buttons will 'pop' up.",
                    position: 'top'
                },
                {
                    element: document.querySelector('.intro-attention'),
                    intro: "If you want your floating action button to have extra attention from your website visitors you can add a attention animation. The button will make a specific 'move' each 10 seconds.",
                    position: 'top'
                },
                {
                    element: document.querySelector('.button.savebutton'),
                    intro: "For now, we are finished here. Click the save button.",
                    position: 'left'
                }
            ]});
        }else if(type == 'buttons') {
            intro.setOptions({
                steps: [
                {
                    element: document.querySelector('#new-button'),
                    intro: "Great! You are ready to make your first Floating Action Button. As an example we will make a Click-to-Call button. To start click on 'New Button'.",
                    position: 'left'
                },
                {
                    element: document.querySelector('#new-button'),
                    intro: "Waiting on you for clicking the button at the right... It says '+ New button'",
                    position: 'left'
                },
                {
                    element: document.querySelector('#new-button'),
                    intro: "Hello? Please click that button...",
                    position: 'left'
                },
                {
                    element: document.querySelector('#new-button'),
                    intro: "Here, on the right... If you won't click it, I will click it >:-)",
                    position: 'left'
                },
                {
                    element: document.querySelector('#new-button'),
                    intro: "Okay. Nothing to say.",
                    position: 'bottom'
                },
            ]});
        }
        else if(type == 'edit-button') {
            intro.setOptions({
                steps: [
                {
                    element: document.querySelector('#' + newButtonId + " .row-info"),
                    intro: "This is the quick-button info bar.<br /><br />When you have multiple buttons, you can drag them to position them on the right place.",
                    position: 'bottom',
                    disableInteraction: true
                },
                {
                    element: document.querySelector('#' + newButtonId + " .row-info .row-title"),
                    intro: "This is the button title. Your webvisitors won't see this name, this is only visible here. We will change the name in 1 steps.",
                    position: 'right',
                    disableInteraction: true
                },
                {
                    element: document.querySelector('#' + newButtonId + " .row-info .desktop-button"),
                    intro: "This is a quick-button to enable the button on desktop. When it's blue it's enabled. When grey it's disabled for desktop.",
                    position: 'left',
                    disableInteraction: true
                },
                {
                    element: document.querySelector('#' + newButtonId + " .row-info .mobile-button"),
                    intro: "There is one for mobile too.",
                    position: 'left',
                    disableInteraction: true
                },
                {
                    element: document.querySelector('#' + newButtonId + " .button_title"),
                    intro: "Create a descent name for your Button like 'Call Button Sales department'. Your webvisitors won't see this name. Although it will appear on Google Analytics on your 'event tracking' section.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('#' + newButtonId + " .button-pdr"),
                    intro: "Choose an appropriate icon or upload your own. Type 'phone' and select the phone icon to give it a phone icon.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('#' + newButtonId + " .button_textfield"),
                    intro: "Optional - Add text If you want to have a label next to your button to explain the button action like 'Call our sales department'. This label will be shown next to the button.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('#' + newButtonId + " .button-action"),
                    intro: "Choose the appropriate click action. When you want to add a click-to-call button select the option phone-number. Type in the phone-number you want your visitors to call when clicking on the button.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('#' + newButtonId + " #button_category"),
                    intro: "Premium function - By default the button is shown on every page. If you want to show your button on selected pages you can add 'page categories'. You need a premium account if you want to do this.",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('#' + newButtonId + " .show-mobile-btn"),
                    intro: "Decide if your button is shown on mobile, desktop or both by selecting the show on mobile and desktop functions. If you don't select at least one of both the button won't show on your website. In the case of a call button we recommend only selecting the option 'show on mobile'. ",
                    position: 'top'
                },
                {
                    element: document.querySelector('#' + newButtonId + " .show-desktop-btn"),
                    intro: "Here you can enable it on your desktop",
                    position: 'bottom'
                },
                {
                    element: document.querySelector('#' + newButtonId + " .must-save-button"),
                    intro: "This is a quick save button. If your button has no changes to it, it'll say 'live'",
                    position: 'left',
                    disableInteraction: true
                },
                {
                    intro: "<b>Congratulations!</b> Your first Floating Action button is ready to go live! Click on save and take a look at the result. Go to your website on your phone or desktop (if you've enabled it there too) and test your button.",
                },
                {
                    intro: "Now it's time to get creative. Add another button on your website like a navigation button or a url to your contact page or maybe even opening Facebook Messenger. <b> It's all up to you </b> ",
                },
                {
                    element: document.querySelector('h2 .savebutton'),
                    intro: "Do you have a feature request, idea or do you need support? Feel free to contact us! Push the save button to finish the tour.",
                    position: 'left',
                },
            ]});
        }

        return intro;
    },
    stickyId: 0,
    sticky: function(type, message) {
        buttonizer.stickyId++;

        var elemDiv = document.createElement('div');
        elemDiv.className = 'buttonizer-sticky '+ type +' sticky-'+ buttonizer.stickyId;
        elemDiv.innerHTML = message;
        document.body.appendChild(elemDiv);


        // document.body.innerHTML += '<div class="buttonizer-sticky '+ type +' sticky-'+ buttonizer.stickyId +'">'+ message +'</div>';

        setTimeout(function() {
            var element = document.querySelector(".sticky-" + buttonizer.stickyId);
            var opacity = 1;

            var fadeOut = setInterval(function() {
                opacity = opacity - 0.05;
                element.style.opacity = opacity;

                if(opacity <= 0) {
                    clearInterval(fadeOut);
                }
            }, 20);
        }, 4000);
    }
};

function checkSelection()
{
    var inputError = false;
    var elements = document.querySelectorAll(".socialSharingCheckbox");
    var warning = document.querySelectorAll(".warning_text");
    var selected = 0;
    for(var i = 0; i < elements.length; i++){
        console.log(elements);
        if(elements[i].checked){
            selected++;
        }
    }
    if(selected < 4){
        for(var d = 0; d < warning.length; d++){
            warning[d].setAttribute("hidden", "hidden");
        }
    }else if(selected > 3){
        for(var o = 0; o < warning.length; o++){
            warning[o].removeAttribute("hidden");
            elements.setAttribute("disabled", "disabled");
        }

    }


}

document.addEventListener('DOMContentLoaded', buttonizer.init, false);

window.onbeforeunload = function(){
    if(document.location.href.indexOf('page=Buttonizer') === -1) {
        return;
    }
    if(buttonizer.settings.hasChanges && !buttonizer.settings.clickedButton) {
        return 'You have unsaved data. Are you sure you want to leave?';
    }
};
