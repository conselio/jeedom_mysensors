jeedom_mysensors
================

1.3 Version stable with local support of serial gateway

1.4 New version with support of jeedom node and network gateway

Add support for JeeNetwork, so serial gateway can be connected to a jeedom node
.
Add support of network gateway (through the nodejs parameters)

Add configuration of serial port using Jeedom usbMapping (for use of udev rules)

Add date and info level in nodejs logs

Add variable path for nodejs (so custom installations not in /ush/share/nginx/www... will work)

Version 1.5

Add SketchName as an information on equipment page

Add SketchVersion as an information on equipment page

Save mySensors library version from presentation if send (mySensors 2.0 will) and from sensors presentation (1.4)

Add BatteryLevel as an information on equipment when node send it

Add related commands to some type of informations :

- relay : S_LIGHT (On and Off)
- dimmer : S_DIMMER (Set dimmer)

Use LogicalID for register node and search them (and also for commands)

Version 1.6

Can restart NodeJS from config page

Better date output in logs to match Jeedom format

Attach a widget for better display on some sensors (temp, hum, light, motion, battery)

Change unit to sensor creation and not value send as it will be the default in 2.0

Automaticly create a device for the gateway with the only purpose to know the status of connexion and use it for alert

Can set a node for monitoring and get an alert on inactive nodes

Version 1.7

Enhancing the configuration page for nodes

Adding widget types and commands for types of sensors needing it

Complete with lib 1.4 (on V_ message and S_ types), waiting for 2.0 for new function on mySensors part for the plugin

Version 1.8

Enhancing configuration page with new template

Correcting one bug on gateway connexion

Adding widgets

Version 1.9

Add commands for Lock

=== Todo : ===

Add inclusion mode in nodejs and jeedom

Add function on plugin to deliver data requested by a node

How to reboot a node from Jeedom
void (softReset){
asm volatile ("  jmp 0");
}

Add order on information and commands
