<?php

require __DIR__ . "/../src/includes/app.php";

$usuario_id = exigir_login();

exibir(["hub/myHub"], ["titulo" => "MyHobbies"]);

?>
