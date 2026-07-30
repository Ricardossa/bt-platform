<?php

declare(strict_types=1);

namespace BT\Core\Upload;

final class Upload
{
    public static function image(
        array $file,
        string $destino,
        array $extensoes = ['png', 'jpg', 'jpeg', 'webp']
    ): ?string
    {
        if (
            !isset($file['tmp_name']) ||
            $file['error'] !== UPLOAD_ERR_OK
        ) {
            return null;
        }

        $ext = strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );

        if (!in_array($ext, $extensoes, true)) {
            return null;
        }

        if (!is_dir($destino)) {
            mkdir($destino, 0755, true);
        }

        $nome = 'logo.' . $ext;

        if (
            move_uploaded_file(
                $file['tmp_name'],
                $destino . '/' . $nome
            )
        ) {
            return $nome;
        }

        return null;
    }
}
