## Transifex Client
Transifex provides a [Client](http://docs.transifex.com/client/) to easily manage and synchronize localization files.
This client is a Command Line Interface tool and must also be installed on your system following the [Client installation](https://docs.transifex.com/client/installing-the-client) guide.

## How to use TX Client with this project?
* First of all, join the [PPDE develop 3.2.x](https://www.transifex.com/signup/?join_project=ppde-develop-32x) project.
* Then, go to your profile and create an [API token](https://www.transifex.com/user/settings/api/) 
* Run the command `tx init --skipsetup` to create the file `.transifexrc`, which stores your Transifex host configuration in your home directory.
* Clone this project from git.
```shell
git clone https://github.com/Skouat/ext_paypal_donation.git
```
* Go tho the project directory and use the proper branch
```shell
cd ext_paypal_donation
git checkout develop-3.2.x
```
* Check that all works as expected (if not check your [Client installation](https://docs.transifex.com/client/installing-the-client)).
```shell
tx status
```
* Get translation language files
```shell
tx pull --mode=translator -l <LN> -r <RESOURCES>
```
* Send translation language files
```shell
tx push -t -l <LN> -r <RESOURCES>
```
### Sample command
```shell
#Pull info_acp_donation.php file from IT language.
tx pull --mode=translator -l it -r ppde-develop-32x.donate_php

#Pull all files from DE language
tx pull --mode=translator -l de

#Push info_acp_donation.php file from IT language
tx push -t -l it -r info_acp_donation_php

#Push all files from DE language and skip errors
tx push -t -l de --skip
```

## TX Client Tooltips
* The name of ressources in stored in `.tx/config`.
* Use the option `-f` will force the download/upload of files.
* Omit the option `-l` will download/upload all language files.
* Omit the option `-r` will download/upload all resources files.
* If you omit `-r` when you push ressources, add the options `--skip` 
