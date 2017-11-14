<?php
namespace Framework;

use Intervention\Image\ImageManager;
use Psr\Http\Message\UploadedFileInterface;

class Upload
{
    protected $path;

    protected $formats;

    public function __construct(?string $path = null)
    {
        if ($path) {
            $this->path = $path;
        }
    }

    public function upload(UploadedFileInterface $file, ?string $oldFile = null): string
    {
        $this->delete($oldFile);
        $targetPath = $this->addCopySuffix($this->path . DIRECTORY_SEPARATOR . $file->getClientFilename());
        $dirname = pathinfo($targetPath, PATHINFO_DIRNAME);
        if (!file_exists($dirname)) {
            mkdir($dirname, 777, true);
        }
        $file->moveTo($targetPath);
        $this->generateFormat($targetPath);

        return pathinfo($targetPath)['basename'];
    }

    private function addCopySuffix($targetPath): string
    {
        if (file_exists($targetPath)) {

            return $this->addCopySuffix($this->getPathWithSuffix($targetPath, 'copy'));
        }

        return $targetPath;
    }

    public function delete(?string $oldFile): void
    {
        if ($oldFile) {
            $oldFile = $this->path . DIRECTORY_SEPARATOR . $oldFile;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
            foreach ($this->formats as $format => $_) {
                $olfFileWithFormat = $this->getPathWithSuffix($oldFile, $format);
                if(file_exists($olfFileWithFormat)) {
                    unlink($olfFileWithFormat);
                }
            }
        }
    }

    private function getPathWithSuffix(string $path, string $suffix): string
    {
        $info = pathinfo($path);

        return $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '_' . $suffix . '.' . $info['extension'];
    }

    private function generateFormat($targetPath)
    {
        foreach ($this->formats as $format => $size) {
            $destination = $this->getPathWithSuffix($targetPath, $format);
            $imageManager = new ImageManager(['driver' => 'imagick']);
            [$width, $height] = $size;
            $imageManager->make($targetPath)->fit($width, $height)->save($destination);
        }
    }
}
