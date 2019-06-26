<?php

namespace App\Service;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    const PRE_SIGN_MAX_EXPIRE_TIME = 10080;

    private $s3Client;

    public function __construct()
    {
        $this->s3Client = new S3Client([
            'credentials' => [
                'key' => 'AKIAIOSFODNN7EXAMPLE',
                'secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            ],
            'endpoint' => 'http://localhost:9000',
            'version' => '2006-03-01',
            'region' => 'us-west-2',
        ]);
    }

    public function upload(UploadedFile $file)
    {
        $filename = $file->getClientOriginalName();

        $this->s3Client->putObject([
            'Bucket' => 'bucket1',
            'Key' => 'boundjobs/jimmy/' . $filename,
            'ContentLength' => $file->getSize(),
            'SourceFile' => $file->getRealPath(),
        ]);
    }

    public function copy($path, $newpath)
    {
        $command = $this->s3Client->getCommand('copyObject', [
            'Bucket' => 'bucket1',
            'Key' => $newpath,
            'CopySource' => rawurlencode('boundjobs/jimmy/' . $path),
        ]);

        try {
            $this->s3Client->execute($command);
        } catch (S3Exception $e) {
            return false;
        }

        return true;
    }

    public function rename($path, $newpath)
    {
        $result = $this->copy($path, $newpath);
        if (! $result) {
            return false;
        }

        dump(compact('result'));

        return $this->delete($path);
    }

    public function delete($path)
    {
        $command = $this->s3Client->getCommand(
            'deleteObject',
            [
                'Bucket' => 'bucket1',
                'Key' => 'boundjobs/' . $path,
            ]
        );

        $this->s3Client->execute($command);
    }

    public function has($path)
    {
        $location = $path;

        if ($this->s3Client->doesBucketExist($this->bucket, $location)) {
            return true;
        }

        return $this->doesDirectoryExist($path);
    }

    public function doesDirectoryExist($location)
    {
        $command = $this->s3Client->getCommand(
            'listObjects',
            [
                'Bucket' => $this->bucket,
                'Prefix' => rtrim($location, '/') . '/',
                'MaxKeys' => 1,
            ]
        );

        try {
            $result = $this->s3Client->execute($command);

            return $result['Contents'] || $result['CommonPrefixes'];
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 403) {
                return false;
            }

            throw $e;
        }
    }
}
