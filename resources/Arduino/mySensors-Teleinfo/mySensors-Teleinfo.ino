// uncomment to enable debuging using serial port @115200bps
//#define DEBUG_ENABLED

// durée entre 2 rafraichissements des données - en mili-secondes (bien laisser UL la fin)
// j'ai un probleme, il met 3 fois plus de temps que ce que j'indique!
// testé sur un arduino pro mini 3.3v/8mhz - est-ce lié????
#define SLEEP_TIME 20000UL

// mysensors
#include <SPI.h>
#include <MySensor.h>

// teleinfo
#include <SoftwareSerial.h>
#define TI_RX 4
#define TI_TX 5

// version du sketch
#define VERSION "0.3"

// longueur max des données qu'on reçoit
#define BUFSIZE 15

MySensor gw;

// paramètres des données téléinfo
// - ne pas toucher :)
// pour mieux comprendre, allez plus bas j'ai ajouté les informations de la doc ERDF afin de s'y retrouver
///////////////////////////////////

// infos générales
//////////////////////////////////
#define CHILD_ID_IINST 3
MyMessage msgIINST( CHILD_ID_IINST, V_CURRENT );

#define CHILD_ID_IMAX 2
MyMessage msgIMAX( CHILD_ID_IMAX, V_CURRENT );

#define CHILD_ID_PAPP 4
MyMessage msgPAPP( CHILD_ID_PAPP, V_WATT ); // pas vrai c'est des VA!

// infos tarif BASE
///////////////////////////////////
#define CHILD_ID_BASE 1
MyMessage msgBASE( CHILD_ID_BASE, V_KWH ); // en fait c'est des WH

// connexion série avec le compteur EDF
SoftwareSerial tiSerial( TI_RX, TI_TX ); // dans les faits, le TX ne sert pas

void setup() {
#ifdef DEBUG_ENABLED
	Serial.begin( 115200 );
#endif

	tiSerial.begin( 1200 );

	gw.begin();
	gw.sendSketchInfo( "Teleinfo Sensor", VERSION );
	gw.present( CHILD_ID_BASE, S_POWER );

	gw.present( CHILD_ID_IMAX, S_POWER );
	gw.present( CHILD_ID_IINST, S_POWER );
	gw.present( CHILD_ID_PAPP, S_POWER );

}

typedef struct TeleInfo TeleInfo;
struct TeleInfo {
	// DOC ERDF - http://www.magdiblog.fr/wp-content/uploads/2014/09/ERDF-NOI-CPT_02E.pdf
	// juste pour memoire, les values
	// BASE : option base
	// HC.. : option heure creuse
	// EJP. : option EJP
	// BBRx : option tempo, x est un char qui indique kkchose (relooooooou les mecs)
	uint32_t BASE; // Index si option = base (en Wh)
	//char PTEC[BUFSIZE]; // Période tarifaire en cours
	// les valeurs de PTEC :
	// - TH.. => Toutes les Heures.
	// - HC.. => Heures Creuses.
	// - HP.. => Heures Pleines.
	// - HN.. => Heures Normales.
	// - PM.. => Heures de Pointe Mobile.
	// - HCJB => Heures Creuses Jours Bleus.
	// - HCJW => Heures Creuses Jours Blancs (White).
	// - HCJR => Heures Creuses Jours Rouges.
	// - HPJB => Heures Pleines Jours Bleus.
	// - HPJW => Heures Pleines Jours Blancs (White).
	// - HPJR => Heures Pleines Jours Rouges
	//char DEMAIN[BUFSIZE]; // Couleur du lendemain si option = tempo
	// valeurs de DEMAIN
	// - ---- : couleur du lendemain non connue
	// - BLEU : le lendemain est jour BLEU.
	// - BLAN : le lendemain est jour BLANC.
	// - ROUG : le lendemain est jour ROUGE.
	uint8_t IINST; // Intensité instantanée (en ampères)
	//uint8_t ADPS; // Avertissement de dépassement de puissance souscrite (en ampères)
	uint8_t IMAX; // Intensité maximale (en ampères)
	uint32_t PAPP; // Puissance apparente (en Volt.ampères)
	//char HHPHC; // Groupe horaire si option = heures creuses ou tempo
	// je comprend pas ce que veulent dire les valeurs de ce truc ... :
	// L'horaire heures pleines/heures creuses (Groupe "HHPHC")
	// est codé par le caractère alphanumérique A, C, D, E ou Y correspondant à la programmation du compteur.
};

// lecture teleinfo
char readTI() {
	while ( !tiSerial.available() );

	return tiSerial.read() & 0x7F;
}

bool atolTI( char *label, char *searchLabel, char *value, uint32_t &last, MyMessage &msg ) {
	uint32_t tmp;

	if ( strcmp( label, searchLabel ) != 0 )
		return false;

	tmp = atol( value );
	if ( last == tmp )
		return true;

#ifdef DEBUG_ENABLED
	Serial.print( label );
	Serial.print( F(" changed from ") );
	Serial.print( last );
	Serial.print( F(" to ") );
	Serial.println( tmp );
#endif

	last = tmp;
	gw.send( msg.set( last ) );

	return true;
}
bool atoiTI( char *label, char *searchLabel, char *value, uint8_t &last, MyMessage &msg ) {
	uint8_t tmp;

	if ( strcmp( label, searchLabel ) != 0 )
		return false;

	tmp = atoi( value );
	if ( last == tmp )
		return true;

#ifdef DEBUG_ENABLED
  Serial.print( label );
  Serial.print( F(" changed from ") );
  Serial.print( last );
  Serial.print( F(" to ") );
  Serial.println( tmp );
#endif

	last = tmp;
	gw.send( msg.set( last ) );

	return true;
}
bool charTI( char *label, char *searchLabel, char &value, char &last, MyMessage &msg ) {
	if ( strcmp( label, searchLabel ) != 0 )
		return false;

	if ( last == value )
		return true;

#ifdef DEBUG_ENABLED
	Serial.print( label );
	Serial.print( F(" changed from ") );
	Serial.print( last );
	Serial.print( F(" to ") );
	Serial.println( value );
#endif

	last = value;
	gw.send( msg.set( last ) );

	return true;
}
bool strTI( char *label, char *searchLabel, char *value, char *last, MyMessage &msg ) {
	if ( strcmp( label, searchLabel ) != 0 )
		return false;

	if ( strcmp( last, value ) == 0 )
		return true;

#ifdef DEBUG_ENABLED
	Serial.print( label );
	Serial.print( F(" changed from ") );
	Serial.print( last );
	Serial.print( F(" to ") );
	Serial.println( value );
#endif

	memset( last, 0, BUFSIZE ); // sembleraie que strcpy ne fasse pas bien son boulot...
	strcpy( last, value );

	gw.send( msg.set( last ) );

	return true;
}

void getTI() {
	static TeleInfo last; // dernière lecture
	char c; // le char qu'on read
	byte nb = 0;

	// enable this softSerial to listening - only if more than one softSerial is in use
	tiSerial.listen();

	// clear the softSerial buffer
	// pas sur que ca serve vraiement en fait...
	if ( tiSerial.overflow() ) {
#ifdef DEBUG_ENABLED
		Serial.println( F( "Serial overflow, flushing datas" ) );
#endif
		// j'ai des doutes, comme le compteur bombarde en permanence, on terminera jamais cette boucle...
		while ( Serial.available() ) {
  			c = tiSerial.read();
#ifdef DEBUG_ENABLED
		Serial.print( c );
#endif
		}
#ifdef DEBUG_ENABLED
		Serial.println( F( "Serial overflow end" ) );
#endif
	}

	// tout d'abord on cherche une fin de ligne
	while ( readTI() != '\n' );

	// maintenant on cherche le label MOTDETAT (cad fin de trame)
	// TIP: c'est le seul qui commence par un M!
	bool sol = true; // start of line
	while ( true ) {
		c = readTI();
		if ( sol && (c == 'M') )
			break;
		sol = ( c == '\n' ); // fin de ligne trouvée, le prochain char sera en debut de ligne donc!
	}

	readline:

	// Cherche une fin de ligne pour etre sur de bien commencer au début d'une ligne ensuite
	while ( readTI() != '\n' );

	uint8_t i; // un compteur
	uint8_t myCS = 32, cs; // le checksum

	// commencer par detecter le label (search for ' ')
	i = 0;
	char label[ BUFSIZE ]; // etiquette
	memset( label, '\0', BUFSIZE );
	while ( true ) {
		c = readTI();
		myCS += (int)c;

		if ( c == ' ' ) break;

		label[ i++ ] = c;
		if ( i == BUFSIZE ) // prevent overflow, it will break the checksum, so silent exit
			break; 
	}

	// rapidement on regarde si c'etait la fin de trame et on skip la suite dans ce cas
	// the end ?
	if ( strcmp( label, "MOTDETAT" ) == 0 ) { // on verifie pas le checksum de cette ligne
#ifdef DEBUG_ENABLED
		Serial.println( F( "------------------------" ) );
		Serial.println( F( "GOT MOTDETAT -  bye " ) );
#endif
		return; // fin de trame
	}

	// la value (search for ' ')
	i = 0;
	char value[ BUFSIZE ];  // la value la plus longue ligne est ADCO / ~15
	memset( value, '\0', BUFSIZE );
	while ( true ) {
		c = readTI();
		myCS += (int)c;

		if ( c == ' ' ) break;

		value[ i++ ] = c;
		if ( i == BUFSIZE ) // prevent overflow, it will break the checksum, so silent exit
			break; 
	}

	// le checksum
	cs = readTI();

#ifdef DEBUG_ENABLED
		Serial.println( F( "------------------------" ) );
		Serial.print( F("GOT LABEL=") );
		Serial.print( label );
		Serial.print( F(" VALUE=") );
		Serial.print( value );
		Serial.print( F(" CHECKSUM=") );
		Serial.println( cs, HEX );
#endif

	// check le checksum
	myCS = (myCS & 0x3F) + 0x20;
	if ( myCS != cs ) { // si c'est pas bon... ben c'est con!
#ifdef DEBUG_ENABLED
		Serial.print( F("CHECKSUM ERROR!!!, MY=") );
		Serial.print( myCS, HEX );
		Serial.print( F(" CHECKSUM=") );
		Serial.println( cs, HEX );
#endif

		goto readline;
	}

	// gestion mySensor, on va caster en fonction du bon type et ensuite vérifier si la value à changée et envoyer le message à la gateway si c'est le cas
	if ( atolTI( label, "BASE", value, last.BASE, msgBASE ) )			goto readline;

	if ( atoiTI( label, "IINST", value, last.IINST, msgIINST ) )		goto readline;
	if ( atoiTI( label, "IMAX", value, last.IMAX, msgIMAX ) )			goto readline;
	if ( atolTI( label, "PAPP", value, last.PAPP, msgPAPP ) )			goto readline;

#ifdef DEBUG_ENABLED
	Serial.print( F( "unkown LABEL=" ) );
	Serial.print( label );
	Serial.print( F( " VALUE=" ) );
	Serial.println( value );	
#endif

	// pour les cas non gérés
	goto readline;
}

void loop() {
	getTI();

	delay( SLEEP_TIME );
	//gw.sleep( SLEEP_TIME );
}
