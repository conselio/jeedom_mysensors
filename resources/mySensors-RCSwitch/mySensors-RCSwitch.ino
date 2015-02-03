/*
  A simple RCSwitch/Ethernet/Webserver demo
  
  http://code.google.com/p/rc-switch/
*/

#include <SPI.h>
#include <Ethernet.h>
#include <RCSwitch.h>

// Ethernet configuration
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED }; // MAC Address
byte ip[] = { 192,168,0, 32 };                        // IP Address
EthernetServer server(80);                           // Server Port 80

// RCSwitch configuration
RCSwitch mySwitch = RCSwitch();
int RCTransmissionPin = 2;

// More to do...
// You should also modify the processCommand() and 
// httpResponseHome() functions to fit your needs.



/**
 * Setup
 */
void setup() {
  Ethernet.begin(mac, ip);
  server.begin();
  mySwitch.enableTransmit( RCTransmissionPin );
}

/**
 * Loop
 */
void loop() {
  char* command = httpServer();
}

/**
 * Command dispatcher
 */
void processCommand(char* command) {
  if        (strcmp(command, "11-on") == 0) {
    mySwitch.switchOn(1,1);
  } else if (strcmp(command, "11-off") == 0) {
    mySwitch.switchOff(1,1);
  }  else if (strcmp(command, "12-on") == 0) {
    mySwitch.switchOn(1,2);
  } else if (strcmp(command, "12-off") == 0) {
    mySwitch.switchOff(1,2);
  } else if (strcmp(command, "13-on") == 0) {
    mySwitch.switchOn(1,3);
  } else if (strcmp(command, "13-off") == 0) {
    mySwitch.switchOff(1,3);
  } else if (strcmp(command, "21-on") == 0) {
    mySwitch.switchOn(2,1);
  } else if (strcmp(command, "21-off") == 0) {
    mySwitch.switchOff(2,1);
  } else if (strcmp(command, "22-on") == 0) {
    mySwitch.switchOn(2,2);
  } else if (strcmp(command, "22-off") == 0) {
    mySwitch.switchOff(2,2);
  } else if (strcmp(command, "23-on") == 0) {
    mySwitch.switchOn(2,3);
  } else if (strcmp(command, "23-off") == 0) {
    mySwitch.switchOff(2,3);
  }
}

/**
 * HTTP Response with homepage
 */
void httpResponseHome(EthernetClient c) {
  c.println("HTTP/1.1 200 OK");
  c.println("Content-Type: text/html");
  c.println();
  c.println("<html>");
  c.println("<head>");
  c.println(    "<title>RCSwitch Webserver Demo</title>");
  c.println(    "<style>");
  c.println(        "body { font-family: Arial, sans-serif; font-size:12px; }");
  c.println(    "</style>");
  c.println("</head>");
  c.println("<body>");
  c.println(    "<h1>RCSwitch Webserver Demo</h1>");
  c.println(    "<ul>");
  c.println(        "<li><a href=\"./?11-on\">Switch 11 on</a></li>");
  c.println(        "<li><a href=\"./?11-off\">Switch 11 off</a></li>");
  c.println(    "</ul>");
  c.println(    "<ul>");
  c.println(        "<li><a href=\"./?12-on\">Switch 12 on</a></li>");
  c.println(        "<li><a href=\"./?12-off\">Switch 12 off</a></li>");
  c.println(    "</ul>");
  c.println(    "<ul>");
  c.println(        "<li><a href=\"./?13-on\">Switch 13 on</a></li>");
  c.println(        "<li><a href=\"./?13-off\">Switch 13 off</a></li>");
  c.println(    "</ul>");
  c.println(    "<ul>");
  c.println(        "<li><a href=\"./?21-on\">Switch 21 on</a></li>");
  c.println(        "<li><a href=\"./?21-off\">Switch 21 off</a></li>");
  c.println(    "</ul>");
  c.println(    "<ul>");
  c.println(        "<li><a href=\"./?22-on\">Switch 22 on</a></li>");
  c.println(        "<li><a href=\"./?22-off\">Switch 22 off</a></li>");
  c.println(    "</ul>");
  c.println(    "<ul>");
  c.println(        "<li><a href=\"./?23-on\">Switch 23 on</a></li>");
  c.println(        "<li><a href=\"./?23-off\">Switch 23 off</a></li>");
  c.println(    "</ul>");
  c.println(    "<hr>");
  c.println(    "<a href=\"http://code.google.com/p/rc-switch/\">http://code.google.com/p/rc-switch/</a>");
  c.println("</body>");
  c.println("</html>");
}

/**
 * HTTP Redirect to homepage
 */
void httpResponseRedirect(EthernetClient c) {
  c.println("HTTP/1.1 301 Found");
  c.println("Location: /");
  c.println();
}

/**
 * HTTP Response 414 error
 * Command must not be longer than 30 characters
 **/
void httpResponse414(EthernetClient c) {
  c.println("HTTP/1.1 414 Request URI too long");
  c.println("Content-Type: text/plain");
  c.println();
  c.println("414 Request URI too long");
}

/**
 * Process HTTP requests, parse first request header line and 
 * call processCommand with GET query string (everything after
 * the ? question mark in the URL).
 */
char*  httpServer() {
  EthernetClient client = server.available();
  if (client) {
    char sReturnCommand[32];
    int nCommandPos=-1;
    sReturnCommand[0] = '\0';
    while (client.connected()) {
      if (client.available()) {
        char c = client.read();
        if ((c == '\n') || (c == ' ' && nCommandPos>-1)) {
          sReturnCommand[nCommandPos] = '\0';
          if (strcmp(sReturnCommand, "\0") == 0) {
            httpResponseHome(client);
          } else {
            processCommand(sReturnCommand);
            httpResponseRedirect(client);
          }
          break;
        }
        if (nCommandPos>-1) {
          sReturnCommand[nCommandPos++] = c;
        }
        if (c == '?' && nCommandPos == -1) {
          nCommandPos = 0;
        }
      }
      if (nCommandPos > 30) {
        httpResponse414(client);
        sReturnCommand[0] = '\0';
        break;
      }
    }
    if (nCommandPos!=-1) {
      sReturnCommand[nCommandPos] = '\0';
    }
    // give the web browser time to receive the data
    delay(1);
    client.stop();
    
    return sReturnCommand;
  }
  return '\0';
}
