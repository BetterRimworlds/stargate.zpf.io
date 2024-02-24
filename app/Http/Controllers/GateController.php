<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\FileExistsException;
use App\Models\Stargate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use SplFileInfo;

class GateController extends \Illuminate\Routing\Controller
{
    /**
     * @throws FileExistsException
     */
    private function UploadFile(UploadedFile $file, string $filename, $disk = 'public'): void
    {
        $path = storage_path('gates');
        if (file_exists("$path/$filename")) {
            throw new FileExistsException('The Stargate already has contents in its buffer.');
        }

        file_put_contents("$path/$filename", $file->get());
    }


    public function register(): Response
    {
        // Regenerate addresses until we find one that doesn't exist (probable) or is older than 30 days.
        $gateAddress = Stargate::registerStargate();

        return new Response($gateAddress);
    }

    public function transmit(Request $request, string $gateAddress): Response
    {
        $path = storage_path('gates');
        $registeredGatePath = "$path/registered/$gateAddress.txt";
        if (!file_exists($registeredGatePath)) {
            return new Response("No stargate is registered at this address.", Response::HTTP_NOT_FOUND);
        }

        $gateBuffer = $request->file('gateBuffer');
        if (!$gateBuffer) {
            return new Response('Missing Stargate contents.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->UploadFile($gateBuffer, $gateAddress . ".xml");

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




