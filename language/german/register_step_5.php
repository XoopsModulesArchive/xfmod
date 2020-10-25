<?php

global $type;
?>
<p>
    Zusätzlich zum vollen <?php echo $type; ?> Namen, musst du noch einen "Kurznamen" angeben für dein <?php echo $type; ?>.
</p>

<P> Der "Kurzname" ist einigen Beschränkungen unterworfen, da er recht oft auf der Site benutzt wird. Die Beschränkungen sind:

<UL>
    <LI>Darf nicht mit einem Kurznamen eines anderen <?php echo $type;
        if ('community' == $type) {
            echo ' or project';
        } ?> übereinstimmen
    <LI>Muss zwischen 3 und 15 Zeichen enthalten
    <LI>Kleinschreibung
    <LI>Darf nur Buchstaben, Zahlen und Bindestriche enthalten
    <LI>Muss ein gültiger Unix-Username sein
    <LI>Darf nicht mit einer unserer reservierten Domains übereinstimmen
    <LI>"Kurzname" wird niemals geändert für <?php echo $type; ?>
</UL>
