<?php
try {
    require 'cabinet/bootstrap.php';

    $init = new \Cabinet\Init();
    echo $init->dispatch();

} catch (\Exception $e) {
    \Cabinet\Error::catchException($e);
}
