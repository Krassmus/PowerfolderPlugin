<?php

require_once __DIR__."/classes/OAuth.class.php";
require_once __DIR__."/classes/PowerfolderFolder.class.php";

class PowerfolderPlugin extends StudIPPlugin implements FilesystemPlugin {

    public function getFileSelectNavigation()
    {
        $nav = new Navigation(_("Powerfolder"));
        $nav->setImage(Icon::create("powerfolder", "clickable"));
        return $nav;
    }

    public function getFolder($folder_id = null)
    {
        $folder_path = explode("/", $folder_id);
        $name = array_pop($folder_path);
        $parent_folder_id = implode("/", $folder_path);
        $folder = new PowerfolderFolder(array(
            'id' => $folder_id,
            'name' => $name,
            'parent_id' => $parent_folder_id,
            'range_type' => $this->getPluginId(),
            'range_id' => $this->getPluginName()
        ), $this->getPluginId());
        return $folder;
    }

    public function download_action()
    {
        $args = func_get_args();
        $file_id = implode("/", $args);

        $url = Config::get()->POWERFOLDER_ENDPOINT ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT;
        if ($url[strlen($url) - 1] !== "/") {
            $url .= "/";
        }
        $webdav = $url . "webdav/";

        $header = array();
        $header[] = "Authorization: Bearer ".\Powerfolder\OAuth::getAccessToken();

        $r = curl_init();
        curl_setopt($r, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($r, CURLOPT_URL, $webdav . $file_id);
        curl_setopt($r, CURLOPT_HTTPHEADER, ($header));
        curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);

        $content = curl_exec($r);
        $info = curl_getinfo($r);
        curl_close($r);

        header("Content-Length: ".$info['size_download']);
        header("Content-Type: ".$info['content_type']);
        echo $content;
        return;
    }

    public function getPreparedFile($file_id, $with_blob = false)
    {
        $folder_path = explode("/", $file_id);
        $filename = array_pop($folder_path);
        $folder_id = implode("/", $folder_path);
        $name = array_pop($folder_path);
        $parent_folder_id = implode("/", $folder_path);

        $folder = new PowerfolderFolder(array(
            'id' => $folder_id,
            'name' => $name,
            'parent_id' => $parent_folder_id,
            'range_type' => $this->getPluginId(),
            'range_id' => $this->getPluginName()
        ), $this->getPluginId());

        foreach ($folder->getFiles() as $file_info) {
            if ($file_info->name === $filename) {
                $info = $file_info;
                break;
            }
        }

        $file = new FileRef();
        $file->id           = $file_id;
        $file->foldertype   = $folder;
        $file->name         = $filename;
        $file->size         = $info->size;
        $file->mime_type    = $info->mime_type;
        $file->download_url = $info->download_url;
        $file->mkdate       = $info->chdate;
        $file->chdate       = $info->chdate;
        $file->content_terms_of_use_id = 'UNDEF_LICENSE';

        if ($with_blob) {
            $parts = parse_url(UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT);
            $url = $parts['scheme']
                .urlencode(UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_USERNAME)
                .":"
                .urlencode(UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_PASSWORD)
                ."@"
                .$parts['host']
                .($parts['port'] ? ":".$parts['port'] : "")
                .($parts['path'] ?: "");
            if ($url[strlen($url) - 1] !== "/") {
                $url .= "/";
            }
            $webdav = $url . "webdav/";


            $header = array();
            $header[] = "Authorization: Bearer ".\Powerfolder\OAuth::getAccessToken();

            $r = curl_init();
            curl_setopt($r, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($r, CURLOPT_URL, $webdav."/".$file_id);
            curl_setopt($r, CURLOPT_HTTPHEADER, ($header));
            curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);

            $content = curl_exec($r);
            $info = curl_getinfo($r);
            curl_close($r);
            $path = $GLOBALS['TMP_PATH']."/".md5(uniqid());
            file_put_contents(
                $path,
                $content
            );
            $file->path_to_blob = $path;
        }

        return $file;
    }

    public function filesystemConfigurationURL()
    {
        return PluginEngine::getURL($this, array(), "configure/myarea");
    }

    public function hasSearch()
    {
        return false;
    }

    public function getSearchParameters()
    {
        // TODO: Implement getSearchParameters() method.
    }

    public function search($text, $parameters = array())
    {
        return null;
    }

    public function isSource()
    {
        return UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ACTIVATED;
    }

    public function isPersonalFileArea()
    {
        return UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ACTIVATED;
    }

    protected function getType($id)
    {
        $parts = parse_url(UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT);
        $url = $parts['scheme']
            ."://"
            .$parts['host']
            .($parts['port'] ? ":".$parts['port'] : "")
            .($parts['path'] ?: "");
        if ($url[strlen($url) - 1] !== "/") {
            $url .= "/";
        }
        $webdav = $url . "webdav/";
        $root = "remote.php/webdav/".$this->id;
        $header = array();
        $header[] = "Authorization: Bearer ".\Powerfolder\OAuth::getAccessToken();
        $r = curl_init();
        curl_setopt($r, CURLOPT_CUSTOMREQUEST, "PROPFIND");
        curl_setopt($r, CURLOPT_URL, $webdav."/".$id);
        curl_setopt($r, CURLOPT_HTTPHEADER, ($header));
        curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
        $xml = curl_exec($r);
        curl_close($r);
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        foreach ($doc->getElementsByTagNameNS("DAV:","response") as $file) {
            foreach ($file->childNodes as $node) {
                if ($node->tagName === "d:propstat") {
                    foreach ($node->childNodes as $prop) {
                        foreach ($prop->childNodes as $attr) {
                            if ($attr->tagName === "d:resourcetype") {
                                $file_attributes['type'] = $attr->childNodes[0] && $attr->childNodes[0]->tagName === "d:collection" ? "folder" : "file";
                            }
                        }
                    }
                }
            }
        }
        return $file_attributes['type'];
    }

    public function isFolder($id)
    {
        return $this->getType($id) == 'folder';
    }

    public function isFile($id)
    {
        return $this->getType($id) == 'file';
    }

}