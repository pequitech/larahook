<?php

namespace Pequitech\Larahook;

use Illuminate\Console\Command;

class HookathonRun extends Command
{

    protected $token;
    protected $bin;
    protected $binUid;
    protected $targetPath;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:hookathonrun {binUid} {targetPath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'HookAthon autoupdate client';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $this->binUid=$this->argument('binUid');
        $this->targetPath=$this->argument('targetPath');
        $this->info('Variable binUid: '.$this->binUid);
        $this->info('Variable targetPath: '.$this->targetPath);
        $this->info('Reaching HookAthon server ...');
        $this->fetchToken();
        $this->info('Instance token: '.$this->token);
        $this->fetchBin();
        foreach ($this->bin->requests as $request) {
          $this->info('request_uid: '.$request->uid);
          $this->dispatchRequest($request);
        }
        //$this->info($this->token);
    }

    /**
     * fetch Token.
     */
    public function fetchToken()
    {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "http://hookathon.herokuapp.com/api/auth/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            "email"=>"mmbdeng@gmail.com",
            "password"=>"nerdzaum12",
          ]),
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/json",
          "X-Requested-With: XMLHttpRequest"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);
      if ($err) {
        $this->error("Error #:" . $err);
      } else {
        $response=json_decode($response);
        $this->token=$response->access_token;
      }
    }

    /**
     * fetch list of bins
     */
    public function fetchBin(){

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "http://hookathon.herokuapp.com/api/bins/".$this->binUid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "Authorization: Bearer {$this->token}",
          "Cache-Control: no-cache",
          "Content-Type: application/json",
          "Postman-Token: a2956801-6ce4-4bd7-91b1-79b1c2e49c39"
          ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        $this->bin=json_decode($response);
      }
    }

    public function dispatchRequest($request){

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->targetPath."/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $request->body,
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/json",
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        echo $response;
      }
    }

}
