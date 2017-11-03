<form action="<?= PluginEngine::getLink($plugin, array(), "configure/myarea") ?>"
      method="post"
      data-dialog
      class="default powerfolder"
      autocomplete="off">

    <fieldset>
        <legend>
            <?= _("Powerfolder konfigurieren") ?>
        </legend>

        <? if (\Powerfolder\OAuth::isReady()) : ?>
            <?= MessageBox::info(_("Powerfolder ist verknüpft")) ?>
        <? elseif(Config::get()->POWERFOLDER_ENDPOINT || UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT) : ?>
            <div style="text-align: center;">
                <?= \Studip\LinkButton::create(_("Powerfolder für Stud.IP freigeben"), PluginEngine::getURL($plugin, array(), "oauth/request_access_token")) ?>
            </div>
        <? endif ?>

        <? if (!Config::get()->POWERFOLDER_ENDPOINT) : ?>
            <label>
                <?= _("Adresse des Powerfolders") ?>
                <input type="text" name="powerfolder[endpoint]" value="<?= htmlReady(UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ENDPOINT) ?>" placeholder="<?= "z.B. https://myserver.tdl/powerfolder" ?>">
            </label>
        <? endif ?>

        <? if (!Config::get()->POWERFOLDER_CLIENT_ID) : ?>
            <label>
                <?= _("App-ID") ?>
                <input type="text" name="powerfolder[client_id]" value="<?= htmlReady(UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_CLIENT_ID) ?>">
            </label>
        <? endif ?>

        <? if (!Config::get()->POWERFOLDER_CLIENT_SECRET) : ?>
            <label>
                <?= _("Secret") ?>
                <input type="text" name="powerfolder[client_secret]" value="<?= htmlReady(UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_CLIENT_SECRET) ?>">
            </label>

            <label>
                <? URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']) ?>
                <?= _("Redirect-URI (zum Eintragen in der Powerfolder)") ?>
                <input type="text" readonly value="<?= htmlReady(PluginEngine::getURL($plugin, array(), "oauth/receive_access_token"), true) ?>">
                <? URLHelper::setBaseURL("/") ?>
            </label>
        <? endif ?>

        <label>
            <input type="checkbox" name="powerfolder[activated]" value="1"<?= UserConfig::get($GLOBALS['user']->id)->POWERFOLDER_ACTIVATED ? " checked" : "" ?>>
            <?= _("Aktiviert") ?>
        </label>
    </fieldset>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>
</form>

<style>
    form.default.powerfolder input[readonly] {
        background-color: #e1e3e4;
        background-image: url(<?= Icon::create("lock-locked", "info_alt")->asImagePath() ?>);
        background-repeat: no-repeat;
        background-position: calc(100% - 5px) 4px;
        background-size: 20px 20px;
    }
</style>