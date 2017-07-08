<?php namespace DryMile\Core\Utils;

class FormKey
{
    private $app;
    private $oldFormKey;
    private $formKeyName;
     
    function __construct( $app ) 
    {
        $this->app          = $app;
        $this->formKeyName  = "form_key";
        if ( $this->app->session()->exists($this->formKeyName) )
        {
            $this->oldFormKey = $this->app->session()->get($this->formKeyName);
        }
    }
 
    public function outputKey()
    {
        $formKey = Secure::generateToken();
        $this->app->session()->set($this->formKeyName, $formKey );
        echo "<input type='hidden' name='".$this->formKeyName."' id='".$this->formKeyName."' value='".$formKey."' />";
    }
 
    public function validate()
    {
        return Input::post('form_key') == $this->oldFormKey ? true : false;
    }
}
