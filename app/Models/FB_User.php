<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 09:23
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class FB_User
 * @property int id
 * @property \Datetime created_at
 * @property \Datetime modified_at
 * @property \Datetime deleted_at
 * @property int created_by
 * @property int modified_by
 * @property int status_enum
 * @property string name
 * @property string email
 * @property string password
 * @property int FC_Country_id
 * @property int gender_enum
 * @property float height
 * @property float weight
 * @property \Datetime dob
 * @property string api_token
 * @property \Datetime api_token_expiry
 * @property string mobile
 * @property int userlevel_enum
 * @property string mobile_udid
 * @property int mobile_platform_type_enum
 * @property string fcm_deviceToken
 * @property string garmin_userId
 * @property string garmin_userAccessToken
 * @property \DateTime garmin_registered_at
 * @property \DateTime garmin_revoked_at
 * @property integer arr_displayed
 * @property integer selected_unit
 */

class FB_User extends Model{

    use Notifiable;
    protected  $table = 'FB_User';

    protected $hidden = ['password','old_password'];

    protected $casts = [
        'height' => 'float',
        'weight' => 'float',
    ];

    public function getFTPCredentials()
    {
        try {
            $DBFTP = new \PDO('mysql:host='.PROFTP_DB_HOST.';charset=utf8mb4;dbname='.PROFTP_DB_USER, PROFTP_DB_USER, PROFTP_DB_PASS);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
        }

        $retrundata = array(
            'status'    => false,
            'error'     => '',
            'username'  => null,
            'password'  => null
        );

        if(!isset($DBFTP))
        {
            $retrundata = array(
                'status'    => false,
                'error'     => 'Cannot connect to data server',
                'username'  => null,
                'password'  => null
            );
        }
        else
        {
            if(empty($this->email))
            {
                $retrundata['error'] = 'No email id found';
            }
            else
            {
                $newpassword = bin2hex(openssl_random_pseudo_bytes(8));
                $newpasswordhash = "{md5}".base64_encode(pack("H*", md5($newpassword)));
                $email = $this->email;

                $stmt = $DBFTP->prepare('SELECT id, userid FROM `ftpuser` WHERE userid = :user_email LIMIT 1');
                $stmt->bindParam(':user_email', $email, \PDO::PARAM_STR); // <-- Automatically sanitized for SQL by PDO
                $stmt->execute();

                $ftpuserData = $stmt->fetch(\PDO::FETCH_ASSOC);
                if(empty($ftpuserData))
                {
                    //create new user
                    $userObj = array(
                        'userid' => $email,
                        'passwd' => $newpasswordhash,
                        'homedir' => '/var/www/ftp-uploads/'.$this->id
                    );

                    $columns = "(";
                    $values = "(";
                    foreach($userObj as $key=>$value)
                    {
                        $columns.=$key.', ';
                        $values.=':'.$key.", ";
                    }

                    $columns = trim($columns,", ").")";
                    $values = trim($values,", ").")";

                    $sql = "INSERT INTO `ftpuser` ".$columns.' VALUES '.$values;

                    $stmt= $DBFTP->prepare($sql);
                    $stmt->execute($userObj);

                    if($DBFTP->lastInsertId() > 0)
                    {
                        $retrundata = array(
                            'status'    => true,
                            'error'     => null,
                            'username'  => $userObj['userid'],
                            'password'  => $newpassword
                        );
                    }
                }
                else
                {
                    //update password
                    $stmt = $DBFTP->prepare('UPDATE `ftpuser` SET passwd = :user_password WHERE userid = :user_email LIMIT 1');
                    $stmt->bindParam(':user_email', $email, \PDO::PARAM_STR); // <-- Automatically sanitized for SQL by PDO
                    $stmt->bindParam(':user_password', $newpasswordhash, \PDO::PARAM_STR); // <-- Automatically sanitized for SQL by PDO
                    $stmt->execute();

                    $retrundata = array(
                        'status'    => true,
                        'error'     => null,
                        'username'  => $email,
                        'password'  => $newpassword
                    );
                }
            }

            $DBFTP = null;
        }


        $this->ftpdetails = $retrundata;

    }

    public function acceptedPolicyRecords()
    {
        return $this->hasMany('\App\Models\FE_UserPolicyAcceptance','FB_User_id','id');
    }

    public function getLatestAcceptedPolicyIds()
    {
        return $this->acceptedPolicyRecords->sortByDesc('updated_at')
                                           ->unique('policy_id')
                                           ->flatten()
                                           ->pluck(['policy_id']);
    }

    public function updatePolicyAcceptance(array $policyIds)
    {
        //get latest versions of the accepted policies
        $latestPoliciesAccepted_versions =  \App\Models\FC_Policy::selectRaw('max(id) as id, policy_id')
                                                                 ->whereIn('policy_id',$policyIds)
                                                                 ->groupBy('policy_id')
                                                                 ->get();

        foreach($latestPoliciesAccepted_versions as $acceptedPolicyVersion)
            {
                \App\Models\FE_UserPolicyAcceptance::updateOrCreate(
                    [
                        'FB_User_id'    => $this->id,
                        'FC_Policy_id'    => $acceptedPolicyVersion->id,
                        'policy_id'    => $acceptedPolicyVersion->policy_id,

                    ]
                );
            }
    }

    function hasDevice()
    {
        return $this->hasMany('FR_User_has_Device','FB_User_id');
    }
    
    function role(){
        return $this->hasOne('\App\Models\FC_Enum','id','userlevel_enum');
    }
    function authorizedUsers()
    {
        return $this->belongsToMany('\App\Models\FB_User','FR_User_has_Authorized_Users','FB_User_id','FB_Authorized_User_id');
    }

}
