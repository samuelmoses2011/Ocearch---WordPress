var buttonizer = {
    settings: {
        buttonizerOpened: false,
        buttonizerHasOpened: false,
        wHeight: 0,
        scrollBarTop: 0,
        showAfter: 0,
        exitIntent: 0,
        exitIntentText: 'Are you looking for this?',
        currentPageUrl: '',
        currentPageTitle: '',
        showedExitIntent: false,
        animated: false,
    },
    init: function(options) {
        options = jQuery.extend({
            scrollBarTop: 0,
            showAfter: 0,
            exitIntent: 0,
            exitIntentText: 'Are you looking for this?'
        }, options);

        this.settings.scrollBarTop = options.scrollBarTop;
        this.settings.showAfter = options.showAfter;
        this.settings.wHeight = Math.max(document.body.scrollHeight, document.body.offsetHeight, document.documentElement.clientHeight, document.documentElement.scrollHeight, document.documentElement.offsetHeight);
        this.settings.exitIntent = options.exitIntent;
        this.settings.exitIntentText = options.exitIntentText;

        this.ready();
    },
    ready: function() {
        if(document.getElementById("buttonizer-sys") != null) {
            document.addEventListener("click", function(e) {
                if(buttonizer.settings.buttonizerOpened && !document.getElementById("buttonizer-button").contains(e.target)) {
                    document.getElementById("buttonizer-sys").className = 'buttonizer_inner';
                    buttonizer.settings.buttonizerOpened = false;

                    if(document.querySelector("#buttonizer-sys .buttonizer_head .text") != null) {
                        document.querySelector("#buttonizer-sys .buttonizer_head .text").remove();
                    }
                }
            }, false);

            document.getElementById("buttonizer-sys").addEventListener("click", function() {
                if(document.querySelector("#buttonizer-sys .buttonizer_head.onlyone") != null) {
                    return;
                }

                if(buttonizer.settings.buttonizerOpened == true) {
                    document.getElementById("buttonizer-sys").className = 'buttonizer_inner';
                    buttonizer.settings.buttonizerOpened = false;

                    if(document.querySelector("#buttonizer-sys .buttonizer_head .text") != null && typeof document.querySelector("#buttonizer-sys .buttonizer_head .text").hasClass != 'undefined') {
                        if(document.querySelector("#buttonizer-sys .buttonizer_head .text").hasClass('noremove')) {
                            document.querySelector("#buttonizer-sys .buttonizer_head .text").remove();
                        }
                    }
                }else{
                    document.getElementById("buttonizer-sys").className = 'buttonizer_inner opened';
                    buttonizer.settings.buttonizerOpened = true;
                }
            });

            // Attenton animation
            if(document.getElementById("buttonizer-button").getAttribute('attention-animation') != 'none') {
                setInterval(function() {
                    if(!buttonizer.settings.buttonizerOpened) {
                        document.getElementById("buttonizer-button").setAttribute("attention-trigger", "true");

                        setTimeout(function() {
                            document.getElementById("buttonizer-button").setAttribute("attention-trigger", "false");
                        }, 2000);
                    }
                }, 10000);
            }
        }

        
/* Premium Code Stripped by Freemius */

    }
};

function onButtonizerClickEvent(text) {
    if (typeof ga == 'function') {
        ga('send', 'event', {
            eventCategory: 'Buttonizer',
            eventAction: document.location.href,
            eventLabel: text
        });
    }
}

function onButtonizerButtonFacebook() {
    window.open('http://www.facebook.com/sharer.php?u='+ document.location.href +'&t='+ document.title +'', 'popupFacebook', 'width=610, height=480, resizable=0, toolbar=0, menubar=0, status=0, location=0, scrollbars=0'); return false;
}

function onButtonizerButtonTwitter() {
    window.open('https://twitter.com/intent/tweet?text='+ document.title + ' Hey! Check out this link:' + '&url='+ document.location.href +'', 'popupTwitter', 'width=610, height=480, resizable=0, toolbar=0, menubar=0, status=0, location=0, scrollbars=0'); return false;
}

function onButtonizerButtonLinkedin() {
    window.open('http://www.linkedin.com/shareArticle?mini=true&url='+ document.location.href +'&title='+ document.title +'&summary='+ document.title +'', 'popupLinkedIn', 'width=610, height=480, resizable=0, toolbar=0, menubar=0, status=0, location=0, scrollbars=0'); return false;
}

function onButtonizerButtonEmail() {
window.location.href = "mailto:?subject=" + document.title + "&body= Hey! Check out this link: " + document.location.href;
}

// Next update, we promise! :)

function onButtonizerButtonWhatsapp() {
    window.open("https://api.whatsapp.com/send?text=" + encodeURI(document.title + " Hey! Check out this link:" + document.location.href));
}
//
// function onButtonizerButtonPinterest() {
//     window.open('http://www.pinterest.com/pin/create/button/?url=/node'+ document.location.href +'&description='+ document.title, 'popupTwitter', 'width=610, height=480, resizable=0, toolbar=0, menubar=0, status=0, location=0, scrollbars=0'); return false;
// }


