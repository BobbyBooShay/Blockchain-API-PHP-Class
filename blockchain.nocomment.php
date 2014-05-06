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
    private $bc_identifier;
    private $password_one;
    private $password_two;
    
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
    
    public function generateAddress( $label=false )
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/new_address";
        
        $arg["password"]        = $this->password_one;
        $arg["second_password"] = $this->password_two;
        
        if($label!==false){ $arg["label"] = $label; }
        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    public function getAddressBalance( $address, $confirmations=_MIN_CONFIRM_ )
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/address_balance";
        
        $arg["password"]      = $this->password_one;
        $arg["address"]       = $address;
        $arg["confirmations"] = $confirmations;
        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    public function getWalletBalance()
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/balance";
        
        $arg["password"] = $this->password_one;
        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    public function sendCoins( $to, $amount, $opt=array() )
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/payment"; 
        
        $arg["password"] = $this->password_one;
        $arg["to"]       = $to;
        $arg["amount"]   = $amount;
        
        if(isset($opt["from"])&&!empty($opt["from"])&&preg_match("/^([13]{1})([A-Za-z0-9]{26,33})$/",$opt["from"])){ $arg["from"]            = $opt["from"]; }
        if(isset($opt["shared"])&&!empty($opt["shared"])&&preg_match("/^[true|false]$/",$opt["shared"])){            $arg["shared"]          = $opt["shared"]; }
        if(isset($opt["fee"])&&!empty($opt["fee"])&&is_numeric($opt["fee"])){                                        $arg["fee"]             = $opt["fee"]; }
        if(isset($opt["note"])&&!empty($opt["note"])){                                                               $arg["note"]            = $opt["note"]; }
        if($this->password_two!==false){                                                                             $arg["second_password"] = $this->password_two; }
        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    public function sendCoinsMulti( $payments, $opt=array() )
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/sendmany";
        
        $arg["password"]   = $this->password_one;
        $arg["recipients"] = json_encode($payments);
        
        if(isset($opt["from"])&&!empty($opt["from"])&&preg_match("/^([13]{1})([A-Za-z0-9]{26,33})$/",$opt["from"])){ $arg["from"]            = $opt["from"];        }
        if(isset($opt["shared"])&&!empty($opt["shared"])&&preg_match("/^[true|false]$/",$opt["shared"]))           { $arg["shared"]          = $opt["shared"];      }
        if(isset($opt["fee"])&&!empty($opt["fee"])&&is_numeric($opt["fee"]))                                       { $arg["fee"]             = $opt["fee"];         }
        if(isset($opt["note"])&&!empty($opt["note"]))                                                              { $arg["note"]            = $opt["note"];        }
        if($this->password_two!==false)                                                                            { $arg["second_password"] = $this->password_two; }
        
        $post_content = $this->urlPost($api_url,$arg);       
        return($post_content);
    }
    
    public function listAddresses( $confirmations=_MIN_CONFIRM_ )
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/list";
        
        $arg["password"]      = $this->password_one;
        $arg["confirmations"] = $confirmations;
        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    public function archiveAddress( $address )
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/archive_address";
        
        $arg["password"]        = $this->password_one;
        $arg["second_password"] = $this->password_two;
        $arg["address"]         = $address;
        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    public function unarchiveAddress( $address )
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/unarchive_address";
        
        $arg["password"]        = $this->password_one;
        $arg["second_password"] = $this->password_two;
        $arg["address"]         = $address;
        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
    public function consolidateAddresses( $days="60" )
    {
        $api_url = "https://blockchain.info/merchant/".($this->bc_identifier)."/auto_consolidate";
        
        $arg["password"]        = $this->password_one;
        $arg["second_password"] = $this->password_two;
        $arg["days"]            = $days;
        
        $post_content = $this->urlPost($api_url,$arg);
        return($post_content);
    }
    
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
