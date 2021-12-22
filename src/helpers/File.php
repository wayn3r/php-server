<?php

namespace Helpers;

trait File {
    static function SaveBase64(
        string $path,
        string $base64,
        string $namePrefix = '',
        string ...$mimes
    ): object {
        $result = new \stdClass;
        $result->filename = '';
        $result->fullpath = '';
        $result->error = false;

        if (!is_dir($path)) {
            $result->error = 'La ruta para guardar el archivo no fue encontrada';
            return $result;
        }

        $_mime_types = [
            // 'text' => ['plain', 'html', 'css', 'javascript'],
            'image' => ['gif', 'png', 'jpeg', 'bmp', 'webp', 'svg+xml'],
            'video' => ['webm', 'ogg'],
            'application' => ['pdf', 'xml']
        ];

        try {
            $_accepted_mime_types = [];
            // buscando los mimes type válidos
            foreach ($mimes as $mime) {
                //la posicion cero debe tener el tipo ejemplo image, y la posicion 1 la extension ejemplo png
                $_mime_keys = explode('/', $mime);
                // El string del mime type pasado en la funcion debe tener un /, 
                // de lo contrario significa que pasaron un mime inválido y es un error en el código
                if (count($_mime_keys) !== 2) {
                    $result->error = "Mime-Type inválido, el tipo de archivo [{$mime}] debe contener un [/]";
                    return $result;
                }
                [$_tipo, $ext] = $_mime_keys;
                if (!isset($_mime_types[$_tipo])) {
                    $result->error = "Mime-Type inválido, el tipo de archivo [{$mime}] no puede ser procesado";
                    return $result;
                }
                // extensiones aceptadas
                $_exts = $_mime_types[$_tipo];
                if ($ext !== '' && !in_array($ext, $_exts)) {
                    $result->error = "Mime-Type inválido, la extensión de archivo [{$ext}] no es aceptada para archivos de tipo [{$_tipo}]";
                    return $result;
                }
                // se agregan los mimes type válidos al array
                if ($ext === '') {
                    $_accepted_mime_types = array_merge(
                        $_accepted_mime_types,
                        array_map(static function ($_extension) use ($_tipo) {
                            return $_tipo . '/' . $_extension;
                        }, $_exts)
                    );
                    continue;
                }
                $_accepted_mime_types[] = $_tipo . '/' . $ext;
            }
            // Se valida que el base64 empiece con el mime-type correcto
            // data:application/pdf;base64,
            $file_mime = substr($base64, 0, strpos($base64, ';'));

            $mime = str_replace('data:', '', $file_mime,);
            if (!in_array($mime, $_accepted_mime_types)) {
                $result->error = "El tipo de archivo [{$mime}] no es un válido";
                return $result;
            }
            //La imagen traerá al inicio data:image/*;base64, cosa que debemos remover
            $archivoCodificadoNoMime = str_replace($file_mime . ';base64,', '',  $base64);

            // En el caso de las imagenes svg, el tipo viene como svg+xml por lo que se divide el texto por el signo de + y se toma la primera posicion
            $extension = explode('/', $mime)[1];
            $extension = explode('+', $extension)[0];

            //Venía en base64 pero sólo la codificamos así para que viajara por la red, ahora la decodificamos y
            //todo el contenido lo guardamos en un archivo
            $imagenDecodificada = base64_decode($archivoCodificadoNoMime, true);
            //Calcular un nombre único
            do {
                $result->filename = $namePrefix . uniqid() . '.' . $extension;
                $result->fullpath = $path . '/' . $result->filename;
            } while (is_file($result->fullpath));
            //Escribir el archivo
            $res = file_put_contents($result->fullpath, $imagenDecodificada);

            if ($res === false) {
                $result->fullpath = '';
                $result->filename = '';
                $result->error = 'Error al ejecutar file_put_contents';
            }
            //Terminar y regresar el nombre del archivo
            return $result;
        } catch (\Throwable $th) {
            $result->filename = '';
            $result->error = $th->getMessage();
            return $result;
        }
    }

    static function SaveBase64Image(string $path, string $base64, string $namePrefix = ''): object {
        return self::SaveBase64($path, $base64, $namePrefix, 'image/');
    }
    static function SaveBase64Pdf(string $path, string $base64, string $namePrefix = ''): object {
        return self::SaveBase64($path, $base64, $namePrefix, 'application/pdf');
    }
    static function RemoveFile(string ...$filename) {
        foreach ($filename as $file) {
            if (is_file($file))
                unlink($file);
        }
    }
}
