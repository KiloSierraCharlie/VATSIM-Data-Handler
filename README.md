# VATSIM Data Handler
Easy to use, object-orientated data handler for the VATSIM status system.

## Requirements
* file_get_contents

## Available content lists
* getPilots() - _Get all pilots connected._
* getSupervisors() - _Everyone connected to VATSIM as a Aupervisor or Administrator._
* getStaff() - _Get all staff members, vACC/ARTCC/Division/Region/Supervisors/BoG/Founders._
* getObservers() - _All observers, including staff & supervisors._
* getAirTraffic() - _All air traffic controllers._
* getVoiceServers() - _Available voice servers._
* getPrefiled() - _Prefiled flightplans._
* getClients() - _Everyone connected to VATSIM._

## How to use
Bear in mind, this only lists callsigns. You might find it more beneficial to var_dump one of the clients, to see the different variables available to you.
```php
<?php
require( "DataHandler.php" );
$DH = new DataHandler();
foreach( $DH->getClients() as $client ){
    print( $client->callsign . "<br/>" );
}
```
