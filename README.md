#Blockchain Wallet API PHP Class

###PHP class for interactions with the [Blockchain Wallet API](https://blockchain.info/api/blockchain_wallet_api)

```php
define("_MIN_CONFIRM_", 6);
class Blockchain
{
    public function __construct( $blockchainid, $pw1, $pw2=false );
    public function changeWallet( $blockchainid, $pw1, $pw2=false );
    
    public function generateAddress( $label=false );
    public function archiveAddress( $address );
    public function unarchiveAddress( $address );
    public function consolidateAddresses( $days="60" );
    public function listAddresses( $confirmations=$min_confirm );
    
    public function getAddressBalance( $address, $confirmations=$min_confirm );
    public function getWalletBalance();
    
    public function sendCoins( $to, $amount, $opt=array() );
    public function sendCoinsMulti( $payments, $opt=array() );
    
}
```

####Initialisation

Set the minimum number of confirmations to consider for transactions in lists and balance checks. Do this with the definition in the main file.

```php
define("_MIN_CONFIRM_", 6);

...

class Blockchain
{
    ...
}
```

Require the class file, create a `Blockchain` wallet object and input your wallet details

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

# Use changeWallet to switch to another wallet

$myWallet->changeWallet( "New Identifier", "New Main Password", "New Second Password" );
```

####Using `generateAddress`

Generates a new address for this wallet. Returns the address and label **or** `false`

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$genAddress = $myWallet->generateAddress( "My new bitcoin address!" );

if( $genAddress !== false )
{
    $newAddress      = $genAddress->address;
    $newAddressLabel = $genAddress->label; # My new bitcoin address!
}
```

####Using `archiveAddress`

> To improve wallet performance addresses which have not been used recently should be moved to an archived state. They will still be held in the wallet but will no longer be included in the "list" or "list-transactions" calls. For example if an invoice is generated for a user once that invoice is paid the address should be archived. Or if a unique bitcoin address is generated for each user, users who have not logged in recently (~30 days) their addresses should be archived.

Returns the successfully archived address **or** `false`

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$archiveAddress = $myWallet->archiveAddress( "1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks" );

if( $archiveAddress !== false )
{
    $archivedAddress = $archiveAddress->archived; # 1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks
}
```

####Using `unarchiveAddress`

> Unarchive an address. Will also restore consolidated addresses (see below).

Returns the successfully unarchived address **or** `false`

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$unarchiveAddress = $myWallet->unarchiveAddress( "1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks" );

if( $unarchiveAddress !== false )
{
    $address = $unarchiveAddress->active; # 1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks
}
```

####Using `consolidateAddresses`

> Queries to wallets with over 10 thousand addresses will become sluggish especially in the web interface. The auto_consolidate command will remove some inactive archived addresses from the wallet and insert them as forwarding addresses (see receive payments API). You will still receive callback notifications for these addresses however they will no longer be part of the main wallet and will be stored server side. If generating a lot of addresses it is a recommended to call this method at least every 48 hours. A good value for days is 60 i.e. addresses which have not received transactions in the last 60 days will be consolidated.

Returns an array of the consolidated addresses **or** `false`

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$consolidate = $myWallet->consolidateAddresses("90");

if( $consolidate !== false )
{
    $consolidatedAddresses = $consolidate->consolidated;
}
```

####Using `listAddresses`

> List all active addresses in a wallet. Also includes a 0 confirmation balance which should be used as an estimate only and will include unconfirmed transactions and possibly double spends.

If you specify a `$min_confirm` then 0-confirmation transactions *will not* be included

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$listAll = $myWallet->listAddresses();

if( $listAll !== false )
{
    foreach( $listAll->addresses as $address )
    {
        echo( "Address: ".       $address->address.       "<br>".
              "Balance: ".       $address->balance.       "<br>".
              "Lbael: ".         $address->label.         "<br>".
              "Total Received: ".$address->total_received."<br><br>" );
    }    
}
```

####Using `getAddressBalance`

> Retreive the balance of a bitcoin address. Querying the balance of an address by label is depreciated.

Returns the balance of that address, the address itself and the total the address has received **or** `false`

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$addressBalance = $myWallet->getAddressBalance( "1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks" );

if( $addressBalance !== false )
{
    $balance        = $addressBalance->balance;
    $address        = $addressBalance->address; # 1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks
    $total_received = $addressBalance->total_received;
}
```

####Using `getWalletBalance`

> Fetch the balance of a wallet. This should be used as an estimate only and will include unconfirmed transactions and possibly double spends.

Returns the balance of the wallet **or** `false`

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$myBalance = $myWallet->getWalletBalance();

if( $myBalance !== false )
{
    $balance = $myBalance->balance;
}
```

####Using `sendCoins`

> Send bitcoin from your wallet to another bitcoin address. All transactions include a 0.0005 BTC miners fee.

All `$options` are *optional*. `from` defaults to nothing, `shared` defaults to false, `fee` defaults to 50000 (0.0005), `note` defaults to nothing. Returns a message like "Sent 0.1 BTC to 1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks", the transaction hash and an additional notice like "Some funds are pending confirmation and cannot be spent yet (Value 0.001 BTC)".

*Divide satoshi by X to get btc and multiply btc by X to get satoshi (Where X = 100,000,000). The fee is added to the amount you want to send and debited from the wallet. So if you are sending 100,000 satoshi with a fee of 50,000 then 150,000 will be removed from your wallet.*

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$options = array( "from"   => "18d3cd2DVzcWMtpwShJGxEcRHx8LRZCShr",
                  "shared" => "false",
                  "fee"    => "55000",
                  "note"   => "Money is not our god");

$sendCoins = $myWallet->sendCoins( "1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks", "124842", $options );

if( $sendCoins !== false )
{
    $message             = $sendCoins->message;
    $tx_hash             = $sendCoins->tx_hash;
    $additional_message  = $sendCoins->notice;
}
```

####Using `sendCoinsMulti`

> Send a transaction to multiple recipients in the same transaction.

Groups transactions in order to minimize transaction fees. `$payments` is required and must be an array with the address as the key and the amount to send in satoshi as the value. `$options` are *optional*, as with `sendCoins()`.

*Divide satoshi by X to get btc and multiply btc by X to get satoshi (Where X = 100,000,000). The fee is added to the amount you want to send and debited from the wallet. So if you are sending 100,000 satoshi to person A, 200,000 to person B and 500,000 to person C with a fee of 50,000 then 850,000 will be removed from your wallet.*

```php
require_once("blockchain.php");

$myWallet = new Blockchain( "Identifier", "Main Password", "Second Password" );

$payments = array( "18d3cd2DVzcWMtpwShJGxEcRHx8LRZCShr" => 300000,
                   "1Gp4K5AnNmT6tdSt5Hv5EArsBBWQi169Ks" => 150000 );

$options = array( "from"   => "18d3cd2DVzcWMtpwShJGxEcRHx8LRZCShr",
                  "shared" => "false",
                  "fee"    => "55000",
                  "note"   => "Money is not our god");

$sendCoins = $myWallet->sendCoinsMulti( $payments, $options );

if( $sendCoins !== false )
{
    $message             = $sendCoins->message;
    $tx_hash             = $sendCoins->tx_hash;
}
```
