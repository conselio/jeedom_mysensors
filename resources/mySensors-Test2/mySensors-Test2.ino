    //          mySensors - Noeud alimenté par piles
    // °°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
    // sketch 2/2 (lancer d'abord le sketch 1/2 pour la calibration)
    // sketch à completer en fonction du capteur utilisé
    // (uniquement la partie gestion des piles est développée)
    //---------------------------------------------------------------

    // INCLUDES
    #include <MySensor.h>
    #include <SPI.h>
    #include <Vcc.h>

    // Initialisations MySensors
    #define ID_BatPcnt 1
    #define ID_Bat 2

    // Initialisations pour la gestion des piles
    // Tension mini en-dessous de laquelle le hardware ne fonctionne plus :
    // La limite est de 1.8V pour les piles alcalines, 1.9V pour le module nRF24L01+
    // 1.8V pour l'ATMega328 en 4 MHz (et moins) et 2.4V en 8MHz :
    const float VccMin   = 1.8;             // Vcc mini attendu, en Volts.
    const float VccMax   = 3.255;           // Vcc Maximum attendu, en Volts (2 piles AA neuves)
    const float VccCorrection = 3.07/3.045; // calibration : Vcc mesuré au multimètre / Vcc mesuré par l'Arduino par vcc.Read_Volts() dans sketch 1/2
    Vcc vcc(VccCorrection);

    // Initialisations générales
    // Durée de sommeil entre deux mesures, en millisecondes
    unsigned long SLEEP_TIME = 825000; // 825000 mS = environ 15 minutes

    // Déclaration de mySensors et définition des messages
    MySensor gw;
    MyMessage msgBAT_PCNT(ID_BatPcnt,V_VAR1); // on utilise le type V_VAR1 pour le % de charge des piles
    MyMessage msgBAT(ID_Bat,V_VAR2);          // on utilise le type V_VAR2 pour la tension des piles

    void setup(void)
    {
      // Lancement de mySensors (mode automatique pour l'attribution des node IDs)
      gw.begin();
      // Envoi du nom et de la version du sketch
      gw.sendSketchInfo("Batteries", "1.0");
      // Présentation des capteurs
      gw.present(ID_BatPcnt, S_CUSTOM);  // type S_CUSTOM pour le capteur "% de charge"
      gw.present(ID_Bat, S_CUSTOM);      // type S_CUSTOM pour le capteur "tension"
    }

    void loop(void)
    {
      // On traite les messages entrants du gateway
      gw.process();
      // mesure de Vcc
      float v = vcc.Read_Volts();
      // calcul du % de charge batterie
      float p = 100 * ((v - VccMin) / (VccMax - VccMin));
      // On envoie les données des capteurs et de l'état de la batterie au Gateway
      //gw.sendBatteryLevel(p);  // Inutile...
      gw.send(msgBAT_PCNT.set(p, 1)); // 1 décimale
      gw.send(msgBAT.set(v, 3));      // 2 décimales
     
     // Changement de l'horlaoge interne : 8 MHz --> 1 MHz (voir datasheet ATMEL section 9.12.2)
      int oldClkPr = CLKPR;  // sauvegarde du registre de prescaler d'horloge CLKPR
      CLKPR = 0x80;          // bit 7 de CLKPR à 1 et les autres à 0 pour indiquer un chagement de prescaler
      CLKPR = 0x03;          // division par 8 de l'horloge 8MHz --> 1MHz
     // on se met en sommeil
      gw.sleep(SLEEP_TIME);
     // Restauration de la fréquence originale à la sortie du mode veille
      CLKPR = 0x80;          // bit 7 de CLKPR à 1 et les autres à 0 pour indiquer un chagement de prescaler
      CLKPR = oldClkPr;      // on restore l'ancien facteur prescaler
    }

