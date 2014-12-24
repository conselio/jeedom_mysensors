#include <MySensor.h> 
#include <SPI.h>
#include <SoftwareSerial.h>


// Mode Debug
#define Debug 0 // mettre 1 pour activer le mode Debug

// Arduino led
#define LedTeleinfo 4
#define LedJeedom 5

/***************** MySensors configuration *******************/

MySensor gw;
MyMessage msgADCO(0,V_VAR1);
MyMessage msgHCHP(1,V_KWH);
MyMessage msgHCHC(2,V_KWH);
MyMessage msgPTEC(3,V_VAR2);
MyMessage msgIINST(4,V_CURRENT);
MyMessage msgIMAX(5,V_CURRENT);
MyMessage msgISOUSC(6,V_CURRENT);
MyMessage msgPAPP(7,V_WATT);
MyMessage msgOPTARIF(8,V_VAR3);


// Duration of a minute in seconds (to allow shorter delay in dev)
#define MINUTE 1

// Teleinfo interval : time in minutes between 2 sends
#define TELEINFO_INTERVAL 1

/***************** Teleinfo configuration part *******************/
char CaractereRecu ='\0';
char Checksum[32] = "";
char Ligne[32]="";
char Etiquette[9] = "";
char Donnee[13] = "";
char Trame[512] ="";
int i = 0;
int j = 0;

unsigned long Chrono = 0;
unsigned long LastChrono = 0;

char ADCO[12] ;      // Adresse du concentrateur de téléreport (numéro de série du compteur), 12 numériques + \0
long HCHC = 0;      // Index option Heures Creuses - Heures Creuses, 8 numériques, Wh
long HCHP = 0;      // Index option Heures Creuses - Heures Pleines, 8 numériques, Wh
char PTEC[4] ;      // Période Tarifaire en cours, 4 alphanumériques
char HHPHC[2] ; // Horaire Heures Pleines Heures Creuses, 1 alphanumérique (A, C, D, E ou Y selon programmation du compteur)
int IINST = 0;     // Monophasé - Intensité Instantanée, 3 numériques, A  (intensité efficace instantanée)
long PAPP = 0;      // Puissance apparente, 5 numérique, VA (arrondie à la dizaine la plus proche)
long IMAX = 0;      // Monophasé - Intensité maximale appelée, 3 numériques, A
char OPTARIF[4] ;    // Option tarifaire choisie, 4 alphanumériques (BASE => Option Base, HC.. => Option Heures Creuses, EJP. => Option EJP, BBRx => Option Tempo [x selon contacts auxiliaires])
char MOTDETAT[10] = "";  // Mot d'état du compteur, 10 alphanumériques
int ISOUSC = 0;    // Intensité souscrite, 2 numériques, A
//Valeur precedante envoyer à Jeedom
boolean ADCOPrev = 0;      // Adresse du concentrateur de téléreport (numéro de série du compteur), 12 numériques + \0
long HCHCPrv = 0;      // Index option Heures Creuses - Heures Creuses, 8 numériques, Wh
long HCHPPrv = 0;      // Index option Heures Creuses - Heures Pleines, 8 numériques, Wh
char PTECPrv[4] ;      // Période Tarifaire en cours, 4 alphanumériques
char HHPHCPrv[2] ; // Horaire Heures Pleines Heures Creuses, 1 alphanumérique (A, C, D, E ou Y selon programmation du compteur)
int IINSTPrv = 0;     // Monophasé - Intensité Instantanée, 3 numériques, A  (intensité efficace instantanée)
long PAPPPrv = 0;      // Puissance apparente, 5 numérique, VA (arrondie à la dizaine la plus proche)
long IMAXPrv = 0;      // Monophasé - Intensité maximale appelée, 3 numériques, A
char OPTARIFPrv[4] ;    // Option tarifaire choisie, 4 alphanumériques (BASE => Option Base, HC.. => Option Heures Creuses, EJP. => Option EJP, BBRx => Option Tempo [x selon contacts auxiliaires])
char MOTDETATPrv[10] = "";  // Mot d'état du compteur, 10 alphanumériques
int ISOUSCPrv = 0;    // Intensité souscrite, 2 numériques, A


int check[11];  // Checksum by etiquette
int trame_ok = 1; // global trame checksum flag
int finTrame=0;
/******************* END OF CONFIGURATION *******************/


/********************* time management **********************/

int second = 0;
int lastTimeHbeat = 0;
int lastTimeTeleinfo = 0;

/*********************** Global vars ************************/

SoftwareSerial cptSerial(2, 3); // Mettre le N° de pin de la liaison serie dans l'ordre (rxPin, txPin)

int packetSize = 0;      // received packet's size
byte remoteIp[4];        // received packet's IP
unsigned int remotePort; // received packet's port

// status
int result;


/********************* Set up arduino ***********************/
void setup() {
       
    // configuration des voyants
    pinMode(LedTeleinfo, OUTPUT);     
    digitalWrite(LedTeleinfo, LOW);
    pinMode(LedJeedom, OUTPUT);
    digitalWrite(LedJeedom, LOW);
   
   
   
    // Serial to EDF cpt
       cptSerial.begin(1200);
   
   
    // Startup and initialize MySensors library. Set callback for incoming messages.
     gw.begin();

    // Send the sketch version information to the gateway and Controller
     gw.sendSketchInfo("Teleinfo", "1.0");
   
    // Present all sensors to controller
 
     gw.present(0, S_POWER);
     gw.present(1, S_POWER);
     gw.present(1, S_POWER);
     gw.present(2, S_POWER);
     gw.present(3, S_POWER);
     gw.present(4, S_POWER);
     gw.present(5, S_POWER);
     gw.present(6, S_POWER);
     gw.present(7, S_POWER);
     gw.present(8, S_POWER);
   
   
   
}

void loop() {
 
           
     
            digitalWrite(LedTeleinfo, HIGH);
            getTeleinfo();
            digitalWrite(LedTeleinfo, LOW);
         delay(1000);
               
   }




/*------------------------------------------------------------------------------*/
/* Test checksum d'un message (Return 1 si checkum ok)            */
/*------------------------------------------------------------------------------*/
int checksum_ok(char *etiquette, char *valeur, char checksum)
{
   unsigned char sum = 32 ;      // Somme des codes ASCII du message + un espace
   int i ;
 
   for (i=0; i < strlen(etiquette); i++) sum = sum + etiquette[i] ;
   for (i=0; i < strlen(valeur); i++) sum = sum + valeur[i] ;
   sum = (sum & 63) + 32 ;
     #ifdef Debug
      Serial.print(etiquette);Serial.print(" ");
        Serial.print(valeur);Serial.print(" ");
       Serial.println(checksum);
       Serial.print("Sum = "); Serial.println(sum);
       Serial.print("Cheksum = "); Serial.println(int(checksum));
    #endif   
   if ( sum == checksum) return 1 ;   // Return 1 si checkum ok.
   return 0 ;
}

/***********************************************
   getTeleinfo
   Decode Teleinfo from serial
   Input : n/a
   Output : n/a
***********************************************/
void getTeleinfo() {
 
  /* vider les infos de la dernière trame lue */
  for(int i = 0; i < 32; i++) Ligne[i] = '\0';//memset(Ligne,'\0',32);
  for(int i = 0; i < 512; i++) Trame[i] = '\0';  //memset(Trame,'\0',512);
  int trameComplete=0;

  for(int i = 0; i < 12; i++) ADCO[i] = '\0';//memset(ADCO,'\0',12);
  HCHC = 0;
  HCHP = 0;
  for(int i = 0; i < 4; i++) PTEC[i] = '\0';//memset(PTEC,'\0',4);
  for(int i = 0; i < 2; i++) HHPHC[i] = '\0';//memset(HHPHC,'\0',2);
  IINST = 0;
  PAPP = 0;
  IMAX = 0;
  for(int i = 0; i < 4; i++) OPTARIF[i] = '\0';//memset(OPTARIF,'\0',4);
  for(int i = 0; i < 10; i++) MOTDETAT[i] = '\0';//memset(MOTDETAT,'\0',10);
  ISOUSC = 0;


  while (!trameComplete){
    while(CaractereRecu != 0x02) // boucle jusqu'a "Start Text 002" début de la trame
    {
       if (cptSerial.available()) {
         digitalWrite(LedTeleinfo,HIGH);
       CaractereRecu = cptSerial.read() & 0x7F;
       digitalWrite(LedTeleinfo,LOW);
       }
    }

    i=0;
    while(CaractereRecu != 0x03) // || !trame_ok ) // Tant qu'on est pas arrivé à "EndText 003" Fin de trame ou que la trame est incomplète
    {
      if (cptSerial.available()) {
         digitalWrite(LedTeleinfo,HIGH);
       CaractereRecu = cptSerial.read() & 0x7F;
       digitalWrite(LedTeleinfo,LOW);
     Trame[i++]=CaractereRecu;
      }   
    }
    finTrame = i;
    Trame[i++]='\0';
   
   if (Debug)Serial.println(Trame);
   
   
   lireTrame(Trame);   

    // on vérifie si on a une trame complète ou non
    for (i=0; i<11; i++) {
      trameComplete+=check[i];
    }
    if (Debug)Serial.print("Nb lignes valides :"); Serial.println(trameComplete);
   if (trameComplete < 11) trameComplete=0; // on a pas les 11 valeurs, il faut lire la trame suivante
    else trameComplete = 1;
  }
}

void lireTrame(char *trame){
    int i;
    int j=0;
    for (i=0; i < strlen(trame); i++){
      if (trame[i] != 0x0D) { // Tant qu'on est pas au CR, c'est qu'on est sur une ligne du groupe
          Ligne[j++]=trame[i];
      }
      else { //On vient de finir de lire une ligne, on la décode (récupération de l'etiquette + valeur + controle checksum
          decodeLigne(Ligne);
          for(int i = 0; i < 32; i++) Ligne[i] = '\0';//memset(Ligne,'\0',32); // on vide la ligne pour la lecture suivante
          j=0;
      }

   }
}

void decodeLigne(char *ligne){
 
  //Checksum='\0';
 
  int debutValeur;
  int debutChecksum;
  // Décomposer en fonction pour lire l'étiquette etc ... 
  debutValeur=lireEtiquette(ligne);
  debutChecksum=lireValeur(ligne, debutValeur);
  lireChecksum(ligne,debutValeur + debutChecksum -1);

  if (checksum_ok(Etiquette, Donnee, Checksum[0])){ // si la ligne est correcte (checksum ok) on affecte la valeur à l'étiquette
    affecteEtiquette(Etiquette,Donnee);
  }}


int lireEtiquette(char *ligne){
    int i;
    int j=0;
    for(int i = 0; i < 9; i++) Etiquette[i] = '\0';//memset(Etiquette,'\0',9);
    for (i=1; i < strlen(ligne); i++){
      if (ligne[i] != 0x20) { // Tant qu'on est pas au SP, c'est qu'on est sur l'étiquette
          Etiquette[j++]=ligne[i];
      }
      else { //On vient de finir de lire une etiquette
       if (Debug)Serial.print("Etiquette : ");Serial.println(Etiquette);
          return j+2; // on est sur le dernier caractère de l'etiquette, il faut passer l'espace aussi (donc +2) pour arriver à la valeur
      }}}


int lireValeur(char *ligne, int offset){
    int i;
    int j=0;
    for(int i = 0; i < 13; i++) Donnee[i] = '\0';//memset(Donnee,'\0',13);
    for (i=offset; i < strlen(ligne); i++){
      if (ligne[i] != 0x20) { // Tant qu'on est pas au SP, c'est qu'on est sur l'étiquette
          Donnee[j++]=ligne[i];
      }
      else { //On vient de finir de lire une etiquette
        if (Debug)Serial.print("Valeur : ");Serial.println(Donnee);
       return j+2; // on est sur le dernier caractère de la valeur, il faut passer l'espace aussi (donc +2) pour arriver à la valeur
      }}}


void lireChecksum(char *ligne, int offset){
    int i;
    int j=0;
    for(int i = 0; i < 32; i++) Checksum[i] = '\0';//memset(Checksum,'\0',32);
    for (i=offset; i < strlen(ligne); i++){
          Checksum[j++]=ligne[i];     
       if(Debug)Serial.print("Chekcsum : ");Serial.println(Checksum);
      }}




void affecteEtiquette(char *etiquette, char *valeur){ // Envoie à jeedom

 if(strcmp(etiquette,"ADCO") == 0) {
   for(int i = 0; i < 12; i++) ADCO[i] = '\0';
   for(int i = 0; i < 12; i++) ADCO[i] = valeur[i];
   check[1]=1;
   if (Debug)Serial.print("ADCO="); Serial.println(ADCO);
   if (ADCOPrev==0){
          digitalWrite(LedJeedom,HIGH); 
        gw.send(msgADCO.set(ADCO));
        digitalWrite(LedJeedom, LOW);
      ADCOPrev++;}
 }
 else if(strcmp(etiquette,"HCHC") == 0) {
   HCHC =atol(valeur); check[2]=1;
   if (Debug)Serial.print("HCHC="); Serial.println(HCHC);
    if (HCHC != HCHCPrv) {
     digitalWrite(LedJeedom,HIGH);
          gw.send(msgHCHC.set(HCHC));
    digitalWrite(LedJeedom,LOW);    
     HCHCPrv = HCHC;
    }
 }
 else if(strcmp(etiquette,"HCHP") == 0) {
  HCHP =atol(valeur); 
  check[3]=1;
   if (Debug)Serial.print("HCHP="); Serial.println(HCHP);
   if (HCHP != HCHPPrv) {
     digitalWrite(LedJeedom,HIGH);
          gw.send(msgHCHP.set(HCHP));
    digitalWrite(LedJeedom,LOW);    
     HCHPPrv = HCHP;
    }
 }
 else if(strcmp(etiquette,"HHPHC") == 0) { // pas transmit à jeedom
   for(int i = 0; i < 2; i++) HHPHC[i] = '\0';
   for(int i = 0; i < 2; i++) HHPHC[i] = valeur[i];
   check[4]=1;
   //memset(HHPHC,'\0',2); strcpy(HHPHC, valeur); check[4]=1;
   if(Debug)Serial.print("HHPHC="); Serial.println(HHPHC);
   }


   else if(strcmp(etiquette,"PTEC") == 0) {
   for(int i = 0; i < 4; i++) PTEC[i] = '\0';
   for(int i = 0; i < 4; i++) PTEC[i] = valeur[i];//memset(PTEC,'\0',4); memcpy(PTEC, valeur,strlen(valeur));
   check[5]=1;
  if(Debug)Serial.print("PTEC="); Serial.println(PTEC);
    if (PTEC[2] != PTECPrv[2]) {
     digitalWrite(LedJeedom,HIGH);
          gw.send(msgPTEC.set(PTEC));
    digitalWrite(LedJeedom,LOW);    
     for(int i = 0; i < 4; i++) PTECPrv[i] = PTEC[i];
    }
 }
 else if(strcmp(Etiquette,"IINST") == 0) {
 IINST = atoi(valeur);
 check[6]=1;
  if(Debug)Serial.print("IINST="); Serial.println(IINST);
   if (IINST != IINSTPrv) {
     digitalWrite(LedJeedom,HIGH);
          gw.send(msgIINST.set(IINST));
    digitalWrite(LedJeedom,LOW);    
     IINSTPrv = IINST;
    }
 }
 else if(strcmp(Etiquette,"PAPP") == 0) {
 PAPP = atol(valeur);
 check[7]=1;
   if(Debug)Serial.print("PAPP="); Serial.println(PAPP);
   if (PAPP != PAPPPrv) {
     digitalWrite(LedJeedom,HIGH);
          gw.send(msgPAPP.set(PAPP));
    digitalWrite(LedJeedom,LOW);    
     PAPPPrv = PAPP;
    }
 }
 else if(strcmp(Etiquette,"IMAX") == 0) {
 IMAX = atol(valeur);
 check[8]=1;
   if(Debug)Serial.print("IMAX="); Serial.println(IMAX);
     if (IMAX != IMAXPrv) {
     digitalWrite(LedJeedom,HIGH);
          gw.send(msgIMAX.set(IMAX));
    digitalWrite(LedJeedom,LOW);    
     IINSTPrv = IMAX;
    }
 }
 else if(strcmp(Etiquette,"OPTARIF") == 0) {
   for(int i = 0; i < 2; i++) OPTARIF[i] = '\0';
   for(int i = 0; i < 2; i++) OPTARIF[i] = valeur[i];
   check[9]=1;
   if(Debug)Serial.print("OPTARIF="); Serial.println(OPTARIF);
     if (OPTARIF[2] != OPTARIFPrv[2]) {
     digitalWrite(LedJeedom,HIGH);
          gw.send(msgOPTARIF.set(OPTARIF));
    digitalWrite(LedJeedom,LOW);    
     for(int i = 0; i < 4; i++) OPTARIFPrv[i] = OPTARIF[i];}
   
 }
 else if(strcmp(Etiquette,"ISOUSC") == 0) {
 ISOUSC = atoi(valeur);
 check[10]=1;
   if(Debug)Serial.print("ISOUSC="); Serial.println(ISOUSC);
   if (ISOUSC != ISOUSCPrv) {
     digitalWrite(LedJeedom,HIGH);
          gw.send(msgISOUSC.set(ISOUSC));
    digitalWrite(LedJeedom,LOW);    
     ISOUSCPrv = ISOUSC;
    }   
 }
 else if(strcmp(Etiquette,"MOTDETAT") == 0) {
    check[11]=1;
   if(Debug)Serial.print("MOTDETAT="); Serial.println(MOTDETAT);
   }
 
}
