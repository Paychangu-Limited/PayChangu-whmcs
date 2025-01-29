
# PayChangu WHMCS Plugin

Welcome to the PayChangu WHMCS plugin repository on GitHub. 

Here you can browse the source code, look at open issues and keep track of development.

## Installation 

### Requirements

- Existing WHMCS installation on your web server.
- Supported Web Servers: Apache and Nginx
- PHP (5.5.19 or more recent) and extensions, MySQL and web browser
- cURL (7.34.0 or more recent)
- OpenSSL v1.0.1 or more recent

### Prepare

- Before you can start taking payments through Paystack, you will first need to sign up at: 
[https://in.paychangu.com/register]. To receive live payments, you should request a Go-live after
you are done with configuration and have successfully made a test payment.

### Install
1. Copy [paychangu.php](modules/gateways/paystack.php?raw=true) in [modules/gateways](modules/gateways) to the `/modules/gateways/` folder of your WHMCS installation.

2. Copy [paychangu.php](modules/gateways/callback/paystack.php?raw=true) in [modules/gateways/callback](modules/gateways/callback) to the `/modules/gateways/callback` folder of your WHMCS installation.

## Documentation

* [PayChangu Documentation](https://developer.paychangu.com/docs)

## Support

For bug reports and feature requests directly related to this plugin, please use the [issue tracker](https://github.com/PaystackHQ/plugin-whmcs/issues). 

## Community

If you are a developer, please join our Developer Community on [Whatsapp](https://chat.whatsapp.com/Hau9JVfjrs34zFuu1zTgpE).

## Contributing to the WHMCS plugin

If you have a patch or have stumbled upon an issue with the WHMCS plugin, you can contribute this back to the code. 
