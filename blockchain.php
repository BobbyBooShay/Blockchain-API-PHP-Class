<?php

define("_MIN_CONFIRM_", 6);

/**
 *  Blockchain Wallet API Class "Blockchain"
 *  https://blockchain.info/api/blockchain_wallet_api
 *
 *  @author     Luke Sims
 *  @license    GPL v2 http://choosealicense.com/licenses/gpl-v2/
 *  @link       https://github.com/lukesims/Blockchain-Wallet-API-PHP-Class
 */
class Blockchain
{
    private $bc_identifier;     ## ($guid) Your identifier for logging in
    private $password_one;      ## Your first password
    private $password_two;      ## Your second password (optional)
    
    public function __construct( $blockchainid, $pw1, $pw2=false )
    {
        $this->bc_identifier = $blockchainid;
        $this->password_one  = $pw1;
        $this->password_two  = $pw2;
    }
    
    public function changeWallet( $blockchainid, $pw1, $pw2=false )
    {
        $this->bc_identifier = $blockchainid;
        $this->password_one  = $pw1;
        $this->password_two  = $pw2;
    }
    
   /**
    *   generateAddress
    **********************
    *   Generates a new receiving address for the current wallet.   
    *
    *   https://blockchain.info/merchant/$guid/new_address?password=$main_password&second_password=$second_password&label=$label
    */
    public function generateAddress( $label=false )
    {
        # Generate new address url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/new_address";
        
        # Default arguments
        $arg["password"]        = $this->password_one;
        $arg["second_password"] = $this->password_two;
        
        # Optional label
        if($label!==false){ $arg["label"] = $label; }
        
        # Make the post request and return response
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
   /**
    *   getAddressBalance
    ************************
    *   Get the balance of a bitcoin address. (Querying the balance of an address by label is depreciated.)
    *
    *   https://blockchain.info/merchant/$guid/address_balance?password=$main_password&address=$address&confirmations=$confirmations
    */
    public function getAddressBalance( $address, $confirmations=_MIN_CONFIRM_ )
    {
        # Get address balance url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/address_balance";
        
        # Arguments
        $arg["password"]      = $this->password_one;
        $arg["address"]       = $address;
        $arg["confirmations"] = $confirmations;
        
        # Make the post request and return response
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
   /**
    *   getWalletBalance
    ***********************
    *   Gets the balance of the whole wallet
    *
    *   From (https://blockchain.info/api/blockchain_wallet_api):
    *   "Fetch the balance of a wallet. This should be used as an estimate only and will include unconfirmed transactions and possibly double spends."
    *
    *   https://blockchain.info/merchant/$guid/balance?password=$main_password
    */
    public function getWalletBalance()
    {
        # Fetch wallet balance url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/balance";
        
        # Password
        $arg["password"] = $this->password_one;
        
        # Make the post request and return response
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
   /**
    *   sendCoins
    ****************
    *   For sending a payment request to the blockchain api
    *
    *   $options in the form: array( "from"   => "Your preferred from address",
    *                                "shared" => "true/false",
    *                                "fee"    => "Fee greater than default of 50000 satoshi (0.0005 btc)",
    *                                "note"   => "Optional public note to include with transaction" )
    *
    *   https://blockchain.info/merchant/$guid/payment?password=$main_password&second_password=$second_password&to=$address&amount=$amount&from=$from&shared=$shared&fee=$fee¬e=$note
    */
    public function sendCoins( $to, $amount, $opt=array() )
    {
        # Outgoing payments url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/payment"; 
        
        # Default arguments
        $arg["password"] = $this->password_one;
        $arg["to"]       = $to;
        $arg["amount"]   = $amount;
        
        # Check if each optional argument is set and in appropriate format
        if(isset($opt["from"])&&!empty($opt["from"])&&preg_match("/^([13]{1})([A-Za-z0-9]{26,33})$/",$opt["from"])){ $arg["from"]            = $opt["from"]; }
        if(isset($opt["shared"])&&!empty($opt["shared"])&&preg_match("/^[true|false]$/",$opt["shared"])){            $arg["shared"]          = $opt["shared"]; }
        if(isset($opt["fee"])&&!empty($opt["fee"])&&is_numeric($opt["fee"])){                                        $arg["fee"]             = $opt["fee"]; }
        if(isset($opt["note"])&&!empty($opt["note"])){                                                               $arg["note"]            = $opt["note"]; }
        if($this->password_two!==false){                                                                             $arg["second_password"] = $this->password_two; }
        
        # Make the post request and return response        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
   /**
    *   sendCoinsMulti
    *********************
    *   For sending multiple payment requests to the blockchain api
    *
    *   $options in the form: array( "from"   => "Your preferred from address",
    *                                "shared" => "true/false",
    *                                "fee"    => "Fee greater than default of 50000 satoshi (0.0005 btc)",
    *                                "note"   => "Optional public note to include with transaction" )
    *
    *   $payments Is a JSON Object using Bitcoin Addresses as keys and the amounts to send as values
    *
    *   https://blockchain.info/merchant/$guid/sendmany?password=$main_password&second_password=$second_password&recipients=$recipients&shared=$shared&fee=$fee
    */
    public function sendCoinsMulti( $payments, $opt=array() )
    {
        # Send many payments url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/sendmany";
        
        # Default arguments
        $arg["password"]   = $this->password_one;
        $arg["recipients"] = json_encode($payments);
        
        # Check if each optional argument is set and in appropriate format
        if(isset($opt["from"])&&!empty($opt["from"])&&preg_match("/^([13]{1})([A-Za-z0-9]{26,33})$/",$opt["from"])){ $arg["from"]            = $opt["from"];        }
        if(isset($opt["shared"])&&!empty($opt["shared"])&&preg_match("/^[true|false]$/",$opt["shared"]))           { $arg["shared"]          = $opt["shared"];      }
        if(isset($opt["fee"])&&!empty($opt["fee"])&&is_numeric($opt["fee"]))                                       { $arg["fee"]             = $opt["fee"];         }
        if(isset($opt["note"])&&!empty($opt["note"]))                                                              { $arg["note"]            = $opt["note"];        }
        if($this->password_two!==false)                                                                            { $arg["second_password"] = $this->password_two; }
        
        # Make the post request and return response
        $post_content = $this->urlPost($api_url,$arg);       
        return($post_content);
    }
    
   /**
    *   listAddresses
    ********************
    *   "List all active addresses in a wallet. Also includes a 0 confirmation balance which 
    *   should be used as an estimate only and will include unconfirmed transactions and possibly double spends."
    *
    *   https://blockchain.info/merchant/$guid/list?password=$main_password
    */
    public function listAddresses( $confirmations=_MIN_CONFIRM_ )
    {
        # List addresses url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/list";
        
        # Arguments
        $arg["password"]      = $this->password_one;
        $arg["confirmations"] = $confirmations;
        
        # Make the post request and return response
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
   /**
    *   archiveAddress
    *********************
    *   Archives the provided address
    *
    *   To improve wallet performance addresses which have not been used recently should 
    *   be moved to an archived state. They will still be held in the wallet but will 
    *   no longer be included in the "list" or "list-transactions" calls. If a unique 
    *   bitcoin address is generated for each user, users who have not logged in 
    *   recently (~30 days) their addresses should be archived.
    *
    *   https://blockchain.info/merchant/$guid/archive_address?password=$main_password&second_password=$second_password&address=$address
    */
    public function archiveAddress( $address )
    {
        # Archive address url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/archive_address";
        
        # Arguments
        $arg["password"]        = $this->password_one;
        $arg["second_password"] = $this->password_two;
        $arg["address"]         = $address;
        
        # Make the post request and return response
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    /**
    *   unarchiveAddress
    ***********************
    *   Unarchive the provided address. Also restores consolidated addresses.
    *
    *   https://blockchain.info/merchant/$guid/unarchive_address?password=$main_password&second_password=$second_password&address=$address
    */
    public function unarchiveAddress( $address )
    {
        # Unarchive address url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/unarchive_address";
        
        # Arguments
        $arg["password"]        = $this->password_one;
        $arg["second_password"] = $this->password_two;
        $arg["address"]         = $address;
        
        # Make the post request and return response
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    /**
    *   consolidateAddresses
    ***************************
    *   Will remove some inactive archived addresses from the wallet and insert them as forwarding addresses (see receive payments API)
    *
    *   Queries to wallets with over 10 thousand addresses will become sluggish especially in the web interface. The auto_consolidate 
    *   command will remove some inactive archived addresses from the wallet and insert them as forwarding addresses (see receive payments API). 
    *   You will still receive callback notifications for these addresses however they will no longer be part of the main wallet 
    *   and will be stored server side. If generating a lot of addresses it is a recommended to call this method at least every 48 hours. 
    *   A good value for days is 60 i.e. addresses which have not received transactions in the last 60 days will be consolidated.
    *
    *   https://blockchain.info/merchant/$guid/auto_consolidate?password=$main_password&second_password=$second_password&days=$days
    */
    public function consolidateAddresses( $days="60" )
    {
        # Consolidation url
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/auto_consolidate";
        
        # Arguments
        $arg["password"]        = $this->password_one;
        $arg["second_password"] = $this->password_two;
        $arg["days"]            = $days;
        
        # Make the post request and return response
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
   /**
    *   urlPost
    **************
    *   For making the requests to blockchain API and returning decoded response
    */
    private function urlPost( $url, $arg )
    {
        $options        = array( "http" => array( "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
                                                  "method"  => "POST", 
                                                  "content" => http_build_query($arg) ) );
        $context        = stream_context_create( $options );
        $json_result    = file_get_contents( $url, false, $context );
        $result         = json_decode( $json_result );
        return($result);
    }
}

?>
