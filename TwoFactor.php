<?php
require_once 'vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

class TwoFactor {
    private $tfa;
    public function __construct() {
        $qrProvider = new QRServerProvider();
        $this->tfa = new TwoFactorAuth($qrProvider, 'Inventory System');
    }

    public function createSecret() {
        return $this->tfa->createSecret();
    }

    public function getSecretURL($label, $secret) {
        return $this->tfa->getQRText($label, $secret);
    }

    public function verifyCode($secret, $code) {
        return $this->tfa->verifyCode($secret, $code);
    }
}
?>
