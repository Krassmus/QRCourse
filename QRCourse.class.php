<?php

class QRCourse extends StudIPPlugin implements SystemPlugin {

    public function __construct()
    {
        parent::__construct();
        bindtextdomain("qrcourse", __DIR__."/locale");
        if (Navigation::hasItem("/course")
                && $GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)
                && stripos($_SERVER['REQUEST_URI'], "plugins.php/cliqrplugin") === false 
                && stripos($_SERVER['REQUEST_URI'], "plugins.php/opencastv3/api") === false)  {
            $this->addStylesheet("assets/qrcourse.less");
            PageLayout::addScript($this->getPluginURL()."/assets/qrcode.min.js");
            PageLayout::addScript($this->getPluginURL()."/assets/qrcourse.js");
            URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
            $url = URLHelper::getScriptURL($_SERVER['REQUEST_URI']);
            if (Config::get()->QRCOURSE_URL_APPENDIX) {
                $params = array();
                $appendix = explode("&", Config::get()->QRCOURSE_URL_APPENDIX);
                foreach ($appendix as $param) {
                    $param = explode("=", $param);
                    $params[urldecode($param[0])] = urldecode($param[1]);
                }
                $url = URLHelper::getScriptURL($url, $params);
            }
            PageLayout::addBodyElements('
                <div style="background-color: white; width: 100%; height: 100%; flex-direction: column; justify-content: center; align-items: center;"
                     id="qr_code">
                    <div>
                        <div style="margin-left: auto; margin-right: auto; text-align: center;" id="qr_code_div"></div>
                    </div>
                    <div class="bottom">
                        ' . Assets::img("logos/logoklein.png", array('style' => "height: 40px;")) . '
                        <span>'.htmlReady($url).'</span>
                    </div>
                </div>
                <script>
                    jQuery(function () {
                        new QRCode(
                            document.getElementById("qr_code_div"), {
                                text: "' . jsReady($url, 'script-double') . '",
                                width: 1280,
                                height: 1280,
                                correctLevel:3
                            }
                        );
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

            NotificationCenter::addObserver($this, "addQRCodeLink", "SidebarWillRender");
        }
    }

    public function addQRCodeLink()
    {
        if (Navigation::getItem("/course")->isActive()) {
            $link = new LinksWidget();
            $link->setTitle(dgettext("qrcourse", "Audience-Response"));
            $link->addLink(
                dgettext("qrcourse", "QR-Code anzeigen"),
                "#",
                Icon::create("code-qr"), array(
                    'onClick' => "STUDIP.QRCourse.showQR(); return false;",
                    'title' => dgettext("qrcourse", "Ihre Studierenden können den QR-Code mit dem Smartphone vom Beamer abscannen und gleich in der Veranstaltung abstimmen oder mitdiskutieren.")
                )
            );
            Sidebar::Get()->addWidget($link);
        }
    }

}
