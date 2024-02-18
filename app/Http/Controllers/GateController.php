<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SplFileInfo;

class GateController extends \Illuminate\Routing\Controller
{
    /**
     * @throws FileExistsException
     */
    private function UploadFile(UploadedFile $file, string $filename, $disk = 'public')
    {
        $path = storage_path('gates');
        if (file_exists("$path/$filename")) {
            throw new FileExistsException('The Stargate already has contents in its buffer.');
        }

        file_put_contents("$path/$filename", $file->get());
    }

    public function transmit(Request $request, string $address)
    {
        $gateBuffer = $request->file('gateBuffer');
        if (!$gateBuffer) {
            return new Response('Missing Stargate contents.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->UploadFile($gateBuffer, $address . ".xml");

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            return new Response($e->getMessage(), Response::HTTP_CONFLICT);
        }
    }

    public function receive(string $address)
    {
        $path = storage_path('gates');
        $filepath = "$path/$address.xml";

        if (!file_exists($filepath)) {
            return new Response('Not a valid gate address.', Response::HTTP_NOT_FOUND);
        }

        $file = new SplFileInfo($filepath);

        $i = 0;
        do {
            ++$i;
            $archivePath = "$path/archive/$address.$i.xml";
        } while (file_exists($archivePath));
        copy($filepath, $archivePath);

        // @see https://stackoverflow.com/a/33001111/430062
        return response()->file($file, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'inline;filename=' . $address . '.xml',
        ])->deleteFileAfterSend();
    }
}




