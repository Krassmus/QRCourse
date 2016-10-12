<?php

class QRCourse extends StudIPPlugin implements SystemPlugin {

    public function __construct()
    {
        parent::__construct();
        if (Navigation::hasItem("/course")
                && $GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
            NotificationCenter::addObserver($this, "addQRCodeLink", "PageWillRender");

        }
    }

    public function addQRCodeLink()
    {
        if (Navigation::getItem("/course")->isActive()) {
            PageLayout::addStylesheet($this->getPluginURL()."/assets/qrcourse.less");
            PageLayout::addScript($this->getPluginURL()."/assets/qrcode.js");
            PageLayout::addScript($this->getPluginURL()."/assets/qrcourse.js");
            URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
            PageLayout::addBodyElements('
                <div style="background-color: white; width: 100%; height: 100%; justify-content: center; align-items: center;"
                     id="qr_code">
                    <img style="width: 90vh; height: 90vh;">
                </div>
                <script>
                    jQuery(function () {
                        var qrcode = new QRCode("'. URLHelper::getLink($_SERVER['REQUEST_URI'], $_GET) .'");
                        var svg = qrcode.svg();
                        jQuery("#qr_code img").attr("src", "data:image/svg+xml;base64," + btoa(svg));
                    });
                    STUDIP.EvaSys = {
                        showQR: function () {
                            var qr = jQuery("#qr_code")[0];
                            if (qr.requestFullscreen) {
                                qr.requestFullscreen();
                            } else if (qr.msRequestFullscreen) {
                                qr.msRequestFullscreen();
                            } else if (qr.mozRequestFullScreen) {
                                qr.mozRequestFullScreen();
                            } else if (qr.webkitRequestFullscreen) {
                                qr.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                            }
                        }
                    };
                </script>
            ');

            $link = new LinksWidget();
            $link->setTitle(_("Audience-Response"));
            $link->addLink(
                _("QR-Code anzeigen"),
                "#",
                Assets::image_path("icons/black/code-qr.svg"), array(
                    'onClick' => "STUDIP.EvaSys.showQR(); return false;"
                )
            );
            Sidebar::Get()->addWidget($link);
        }
    }

}