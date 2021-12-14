<?php


namespace App\Models;
use DOMDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer $id
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property \DateTime $deleted_at
 * @property string $title
 * @property \DateTime $published_on
 * @property string $content
 * @property string $url
 * @property integer $views
 * @property integer $author_id
 * @property string $thumbnail_uri
 */
class FB_Community_post extends Model
{
    protected  $table = 'FB_Community_post';
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'published_on',
    ];

    public function hasManyTags()
    {
        return $this->belongsToMany('App\Models\FB_Community_tag','FR_Community_posts_has_tags','FB_Community_Posts_id','FB_Community_Tags_id');
    }


    public function getMetaUrlData()
    {
        $metaDataToGet = array(
            'og:title' =>'',
            'og:description' =>'',
            'og:image'  =>''
        );


        $data = $this->file_get_contents_curl($this->url);
        // Load HTML to DOM Object
        $dom = new DOMDocument();
        @$dom->loadHTML($data);

        // Parse DOM to get Meta Description
        $metas = $dom->getElementsByTagName('meta');
        for ($i = 0; $i < $metas->length; $i ++) {
            $meta = $metas->item($i);
            foreach(array_keys($metaDataToGet) as $dataname)
            {
                if ($meta->getAttribute('name') == $dataname) {
                    $metaDataToGet[$dataname] = $meta->getAttribute('content');
                }
                //if we find a property matching, then override it
                if ($meta->getAttribute('property') == $dataname) {
                    $metaDataToGet[$dataname] = $meta->getAttribute('content');
                }
            }

        }



        // Parse DOM to get Images
        $image_src = array();
        $images = $dom->getElementsByTagName('img');

        for ($i = 0; $i < $images->length; $i ++) {
            $image = $images->item($i);
            $src = $image->getAttribute('src');

            if(filter_var($src, FILTER_VALIDATE_URL)) {
                $image_src[] = $src;
            }
        }

        //check and set values
        if(!empty($metaDataToGet['og:description']))
        {
            $this->content = $metaDataToGet['og:description'];
        }
        //check where the image is set
        if(!empty($metaDataToGet['og:image'])) //we prefer the OG image
        {
            $this->thumbnail_uri = $metaDataToGet['og:image'];
        }
        else if(!empty($image_src)) //if no OG image is set, then check the content images
        {
            $this->thumbnail_uri = array_pop($image_src);
        }
        else
        {
            $this->thumbnail_uri = COMMUNITY_POST_DEFAULT_THUMBNAIL;
        }

    }

    private function file_get_contents_curl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}
