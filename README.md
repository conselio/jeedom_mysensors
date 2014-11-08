jeedom_mysensors
================

1.3 Version stable with local support of serial gateway

1.4 New version with support of jeedom node and network gateway
Add support for JeeNetwork, so serial gateway can be connected to a jeedom node.
Add support of network gateway (through the nodejs parameters)
Add configuration of serial port using Jeedom usbMapping (for use of udev rules)
Add date and info level in nodejs logs
Add variable path for nodejs (so custom installations not in /ush/share/nginx/www... will work)

** Todo : **
Add SketchName as an information on equipment page
Add SketchVersion as an information on equipment page
Add BatteryLevel as an information on equipment
Add related commands to some type of informations :
- relay : S_LIGHT
- dimmer : S_DIMMER
Save mySensors library version from presentation
