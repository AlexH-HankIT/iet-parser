<?php

namespace MrCrankHank\IetParser\Interfaces;

interface GlobalOptionParserInterface
{
    public function add($option);

    public function delete($option);

    public function addIncomingUser($username, $password);

    public function addOutgoingUser($username, $password);

    public function deleteIncomingUser($username, $password);

    public function deleteOutgoingUser($username, $password);
}
