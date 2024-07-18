<?php
/**
 * Encryptor
 */
namespace tinymeng\OAuth2\Helper;

class Encryptor{

    /**
     * 解密数据（微信小程序手机号登）
     * @param string $sessionKey
     * @param string $iv
     * @param string $encrypted
     * @return array
     * @throws \Exception
     */
    public function decryptData(string $sessionKey, string $iv, string $encrypted): array
    {
        $decrypted = AES::decrypt(
            base64_decode($encrypted, false),
            base64_decode($sessionKey, false),
            base64_decode($iv, false)
        );

        $decrypted = json_decode($decrypted, true);

        if (!$decrypted) {
            throw new \Exception("The given payload is invalid.");
        }

        return $decrypted;
    }
}
