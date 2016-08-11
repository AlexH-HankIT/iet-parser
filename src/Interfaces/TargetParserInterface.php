<?php

namespace MrCrankHank\IetParser\Interfaces;

use League\Flysystem\FilesystemInterface;

interface TargetParserInterface
{
    public function __construct(FilesystemInterface $filesystem, $filePath, $target = null);

    public function addTarget();

    public function deleteTarget();

    public function addOption($option);

    public function deleteOption($option);

    public function getOptions();

    public function getLun($id = false);

    public function addLun($path, $type = 'fileio', $scsiId = null, $scsiSN = null, $ioMode = null, $blockSize = null);

    public function deleteLun($id);

    public function addOutgoingUser($user, $password);

    public function deleteOutgoingUser($user, $password);

    public function addIncomingUser($user, $password);

    public function deleteIncomingUser($user, $password);
}