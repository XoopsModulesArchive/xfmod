<?php

global $type;

global $utype;

?>

<p>

    Das ist die Beschreibung deines <?php echo $type; ?> welches auf den <?php echo $utype; ?> Zusammenfassungsseiten, in den Suchergebnissen, etc. auftaucht.

    <?php

    if ('community' != $type) {
        echo "Es sollte nicht so umfassend und formell wie <?php echo $utype; ?> Beschreibung (Schritt 2), also f";
    } else {
        echo 'F';
    }

    ?>

    inde knappe und erklärende Worte.

</p>
