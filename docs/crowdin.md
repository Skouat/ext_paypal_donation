## Crowdin Client
Crowdin provides a [Client](https://support.crowdin.com/cli-tool/) to easily manage and synchronize localization files.
This client is a Command Line Interface tool and must also be installed on your system following the [Client installation](https://support.crowdin.com/cli-tool/) guide.

## How to use Crowdin Console Client with this project?
  - First, join the [PayPal Donation Extension](https://crwd.in/skouat-ppde) project.  
    Then, go to your Crowdin profile and create an [Personal Access Token](https://crowdin.com/settings#api-key)
  - Create your own API credential config file in `$HOME/.crowdin.yaml`.  
    Then add your Personal Token ID in this file.  
    ```
    "api_token": "{your-token}"
    ```
  - Clone this project from git.
    ```shell
    git clone https://github.com/Skouat/ext_paypal_donation.git
    ```
  - Go tho the project directory and use the proper branch
    ```shell
    cd ext_paypal_donation
    git checkout develop-3.3.x
    ```
  - Check that all works as expected (if not check your [Client installation](https://support.crowdin.com/cli-tool/)).
    ```shell
    crowdin status
    ```
  - Get translation language files
    ```shell
    crowdin pull -l {LN} -b {branch_name}
    ```
  - Send translation language files
    ```shell
    crowdin push translations -l {LN} -b {branch_name}
    ```
### Useful commands
```shell
# List all available languages code.
crowdin status

# List all available branches
crowdin list branches
```
