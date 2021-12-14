<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/***
 * Class FC_Policy
 *
 * @package App\Models
 * @property integer id
 * @property integer policy_id
 * @property string name
 * @property string content
 * @property string url
 * @property float version
 * @property string description
 * @property boolean is_displayed
 * @property boolean is_mandatory
 * @property \DateTime created_at
 * @property \DateTime updated_at
 */
class FC_Policy extends Model
{
    protected $table = "FC_Policy";

    function acceptedByUsers()
    {
        return $this->hasMany('\App\Models\FE_UserPolicyAcceptance','FC_Policy_id','id');
    }
    
    function getDescriptionRefDictionary()
    {
        
        $dom = new \DOMDocument();
        @$dom->loadHTML($this->description);
        $links = $dom->getElementsByTagName('a');
    
        $refdic = [];
        foreach ($links as $link){
            //Extract and show the "href" attribute.
            $refdic[] = ['refText' => $link->nodeValue,
                         'refURL'   => $link->getAttribute('href')
                        ];
        }
        
        return [
            "html_text"    => $this->description,
            "plain_text"    => strip_tags($this->description),
            "refdic"        =>$refdic
        ];
    }
}
