    //                             mySensors - Noeud alimenté par piles
    // °°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°
    // sketch 1/2 (à lancer avant le sketch 2/2 pour la calibration)
    //
    // mode d'emploi :
    //  1) téléverser le sketch dans l'arduino
    //  2) mesurer la tension aux bornes des piles ou entre pin Vcc et GND de l'arduino, noter la valeur mesurée
    //  3) afficher le moniteur série et noter la tension VCC affichée
    //  4) modifier le sketch : changer la ligne "const float VccCorrection = 1.0/1.0;" en :
    //     "const float VccCorrection = Vcc mesuré / Vcc affiché;"
    //      Vcc mesuré et Vcc affiché étant les valeurs notées aux étapes 2) et 3)
    //  5) téléverser cettte version du sketch modifié et relancer le moniteur série. La valeur VCC affichée
    //     doit maintenant etre égale à votre tension mesurée
    //  6) reporter ces valeurs dans le sketch 2/2 dans la ligne "const float VccCorrection = 1.0/1.0;
    //
    //  Exemple : tension mesurée au multimètre  : 3.07v
    //            valeur affichée par le sketch avec les facteurs 1.0/1.0 : VCC = 3.045 Volts
    //            La ligne doit etre modifiée comme suit : "const float VccCorrection = 3.07/3.045;"
    //------------------------------------------------------------------------------------------------------------

    #include <Vcc.h>

    const float VccCorrection = 1.0/1.0;  // calibration : Vcc mesuré / Vcc affiché par l'Arduino

    Vcc vcc(VccCorrection);

    void setup()
    {
      Serial.begin(115200);
    }

    void loop()
    { 
      float v = vcc.Read_Volts();
      Serial.print("VCC = ");
      Serial.print(v);
      Serial.println(" Volts");
      delay(2000);
    }

