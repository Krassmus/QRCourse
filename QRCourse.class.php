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
            $this->addStylesheet("assets/qrcourse.less");
            PageLayout::addScript($this->getPluginURL()."/assets/qrcode.js");
            PageLayout::addScript($this->getPluginURL()."/assets/qrcourse.js");
            URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
            $url = URLHelper::getLink($_SERVER['REQUEST_URI'], $_GET);
            PageLayout::addBodyElements('
                <div style="background-color: white; width: 100%; height: 100%; flex-direction: column; justify-content: center; align-items: center;"
                     id="qr_code">
                    <div>
                        <img style="width: 90vh; height: 90vh;" class="qr_code">
                    </div>
                    <div>
                        ' . Assets::img("logos/logoklein.png", array('style' => "vertical-align: middle; height: 40px;")) . '
                        '.$url.'
                    </div>
                </div>
                <script>
                    jQuery(function () {
                        var qrcode = new QRCode("'. $url .'");
                        var svg = qrcode.svg();
                        jQuery("#qr_code img.qr_code").attr("src", "data:image/svg+xml;base64," + btoa(svg));
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
                    'onClick' => "STUDIP.EvaSys.showQR(); return false;",
                    'title' => _("Ihre Studierenden können den QR-Code mit dem Smartphone vom Beamer abscannen und gleich in der Veranstaltung abstimmen oder mitdiskutieren.")
                )
            );
            Sidebar::Get()->addWidget($link);
        }
    }

}