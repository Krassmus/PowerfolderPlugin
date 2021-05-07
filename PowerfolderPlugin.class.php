<?php

require_once __DIR__."/classes/OAuth.class.php";
require_once __DIR__."/classes/PowerfolderFile.php";
require_once __DIR__."/classes/PowerfolderFolder.php";

class PowerfolderPlugin extends StudIPPlugin implements FilesystemPlugin {

    public function getFileSelectNavigation()
    {
        $nav = new Navigation(_("Powerfolder"));
        $nav->setImage(Icon::create("powerfolder", "clickable"));
        return $nav;
    }

    public function getFolder($folder_id = null)
    {
        Navigation::activateItem('/files/my_files');
        if ($folder_id[0] === "/") {
            $folder_id = substr($folder_id, 1);
        }
        if ($folder_id && !$this->isFolder($folder_id)) {
            return null;
        }

        $folder_path = explode("/", $folder_id);
        $name = rawurldecode(array_pop($folder_path));
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
        $file_id = implode("/", array_map("rawurlencode", $args));

        $url = Config::get()->POWERFOLDER_ENDPOINT ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT_USER;
        if ($url[strlen($url) - 1] !== "/") {
            $url .= "/";
        }
        $webdav = $url . "webdav/";

        $header = array();
        $header[] = PowerfolderFolder::getAuthHeader();

        $r = curl_init();
        curl_setopt($r, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($r, CURLOPT_URL, $webdav . $file_id);
        curl_setopt($r, CURLOPT_HTTPHEADER, ($header));
        curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($r, CURLOPT_SSL_VERIFYPEER, (bool) Config::get()->POWERFOLDER_SSL_VERIFYPEER);
        curl_setopt($r, CURLOPT_SSL_VERIFYHOST, (bool) Config::get()->POWERFOLDER_SSL_VERIFYPEER);
        if ($GLOBALS['POWERFOLDER_VERBOSE']) {
            curl_setopt($r, CURLOPT_VERBOSE, true);
        }

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
        if (!$this->isFile($file_id)) {
            return null;
        }

        $folder_path = explode("/", $file_id);
        $folder_path = array_map("rawurldecode", $folder_path);
        $filename = array_pop($folder_path);
        $folder_id = implode("/", array_map("rawurlencode", $folder_path));
        $name = array_pop($folder_path);
        $parent_folder_id = implode("/", array_map("rawurlencode", $folder_path));

        $data = [
            'id' => $folder_id,
            'name' => $name,
            'parent_id' => $parent_folder_id,
            'range_type' => $this->getPluginId(),
            'range_id' => $this->getPluginName()
        ];

        $folder = new PowerfolderFolder(
            $data,
            $this->getPluginId()
        );

        foreach ($folder->getFiles() as $file) {
            if ($file->getFilename() === $filename) {
                return $file;
            }
        }

        return null;
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
        $url = Config::get()->POWERFOLDER_ENDPOINT ?: UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT_USER;
        if ($url[strlen($url) - 1] !== "/") {
            $url .= "/";
        }
        $webdav = $url . "webdav/";
        $header = array();
        $header[] = PowerfolderFolder::getAuthHeader();
        $r = curl_init();
        curl_setopt($r, CURLOPT_CUSTOMREQUEST, "PROPFIND");
        curl_setopt($r, CURLOPT_URL, $webdav . $id);
        curl_setopt($r, CURLOPT_HTTPHEADER, ($header));
        curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($r, CURLOPT_SSL_VERIFYPEER, (bool) Config::get()->POWERFOLDER_SSL_VERIFYPEER);
        curl_setopt($r, CURLOPT_SSL_VERIFYHOST, (bool) Config::get()->POWERFOLDER_SSL_VERIFYPEER);
        if ($GLOBALS['POWERFOLDER_VERBOSE']) {
            curl_setopt($r, CURLOPT_VERBOSE, true);
        }
        $xml = curl_exec($r);
        curl_close($r);
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $href = "/webdav/" . $id;

        foreach ($doc->getElementsByTagNameNS("DAV:","response") as $file) {
            $is_current_id = false;
            if ($file->childNodes) {
                foreach ($file->childNodes as $node) {
                    if (strtolower($node->tagName) === "d:href") {
                        $node_href = $node->nodeValue;
                        if ($node_href[strlen($node_href) - 1] === "/") {
                            $node_href = substr($node_href, 0, -1);
                        }
                        $node_href = str_replace(array("(", ")", "'", "$", "+", "!", "*", ","), array("%28", "%29", "%27", "%24", "%2B", "%21", "%2A", "%2C"), $node_href); //powerfolder only
                        if ($node_href === $href) {
                            $is_current_id = true;
                            break;
                        }
                    }
                }
            }
            if ($is_current_id && $file->childNodes) {
                foreach ($file->childNodes as $node) {
                    if (strtolower($node->tagName) === "d:propstat") {
                        if ($node->childNodes) {
                            foreach ($node->childNodes as $prop) {
                                if ($prop->childNodes) {
                                    foreach ($prop->childNodes as $attr) {
                                        if (strtolower($attr->tagName) === "d:resourcetype") {
                                            return ($attr->childNodes[0] && strtolower($attr->childNodes[0]->tagName) === "d:collection")
                                                ? "folder"
                                                : "file";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    //$-_.+!*'(),

    public function isFolder($id)
    {
        return $this->getType($id) == 'folder';
    }

    public function isFile($id)
    {
        return $this->getType($id) == 'file';
    }

}
