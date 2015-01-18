jeedom_mysensors
================

=== Changelog ===

Voir : https://github.com/lunarok/jeedom_mysensors/blob/master/doc/fr_FR/changelog.asciidoc

=== Todo ===

Add function on plugin to deliver data requested by a node

How to reboot a node from Jeedom
void (softReset){
asm volatile ("  jmp 0");
}

Send libversion at presentation including gateway

Have a way for each node to listen for reboot (of course sleeping node will not answering)

Data type is not needed and sensor types must be more globals (ie. IR_SEND must become REMOTE, no type, no reference to a technology)

Load sketch with avrdude

Send sensor name in presentation payload

Send the power source by node, maybe in battery
