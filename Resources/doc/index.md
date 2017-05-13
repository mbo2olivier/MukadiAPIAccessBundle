Getting Started With MukadiAPIAccessBundle
==========================================

The Symfony Security component provides a flexible way to authenticate User with API Keys via the `SimplePreAuthenticatorInterface`
which allow you to implement you authentication mecanism really easily. 
The MukadiAPIAccessBundle builds on top of this to make it quick and easy to manage clients.


Step 1: Install and Enable the bundle
-------------------------------------

Require the bundle with composer:

.. code-block:: bash

    $ composer require mukadi/api-access-bundle "^1.*"
    
Enable the bundle in the kernel::

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Mukadi\APIAccessBundle\MukadiAPIAccessBundle(),
            // ...
        );
    }

Step 2: Create your Client class
--------------------------------
Your first job is to create the ``Client`` class for your API.
The bundle provide a bases client classes (one for each storage layer)
which are already mapped for API keys fields to make it easier
to create your entity, this class can look and act however you want:
add any properties or methods you find useful.
You can create all client class you wan't. Here is steps for create client class: 

1. Extend the base ``Client`` class (the class to use depends of your storage layer)
2. Map the ``id`` field. It must be protected as it is inherited from the parent class.
3. Implement the ``getClientName`` method which may return the client identifier for
make a distinction between the application's API clients.

.. note::

    When The client name is used by the bundle to generate the default API Client ROLE in the application.
    For some given client name, the generate role look like `ROLE_[YOUR_CLIENT_NAME_IN_LOWERCASE]_API_CLIENT`.

.. caution::

    When you extend from the mapped superclass provided by the bundle, don't
    redefine the mapping for the other fields as it is provided by the bundle.
    
.. caution::

    Nowadays, MukadiAPIAccessBundle support Doctrine ORM only, others storage driver
    will be added in next version.

a) Doctrine ORM User class

If you're using the Doctrine ORM as persistence layer, then your ``Client`` class
should live in the ``Entity`` namespace of your bundle and look like this to
start:

.. configuration-block::

    .. code-block:: php-annotations
    <?php
    // src/AppBundle/Entity/Client.php
    namespace AppBundle\Entity;
    
    use Doctrine\ORM\Mapping as ORM;
    use Mukadi\APIAccessBundle\Entity\Client as BaseClient;
    
    /**
     * @ORM\Table(name="api_client")
     * @ORM\Entity()
     */
    class Client extends BaseClient{
       /**
       * @ORM\Column(name="id", type="integer")
       * @ORM\Id
       * @ORM\GeneratedValue(strategy="AUTO")
       */
       protected $id;
       
       public function __construct()
       {
          parent::__construct();
          // your own logic
       }
       
       public function getClientName()
       {
           return "my_api_client";
       }
    }
    
Step 3: Configure the bundle
----------------------------

Now that you have properly create your API Client class, the next step is to register it
in the bundle, then the MukadiAPIAccessBundle will create for you a client manager and a provider
if not explicitly specified. This manager and provider will be used for handle client authentication
in the application security layer.

The client manager and provider are accessible in the application service container from the service
referenced as `mukadi_api_access.[YOUR_CLIENT_NAME_IN_LOWERCASE].client_manager` and
`mukadi_api_access.[YOUR_CLIENT_NAME_IN_LOWERCASE].client_provider`.

For configure the MukadiAPIAccessBundle add the following configuration to your ``config.yml`` file.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        mukadi_api_access:
            driver: orm # at the present version only orm value is supported
            clients: # register all clients you have created under this section
                my_api_client: # the value returned by the `getClientName` method
                    client_class: AppBundle\Entity\Client # the client class you have created
       
Step 4: Build your client authenticator
---------------------------------------

The MukadiAPIAccessBundle provide the ``ApiAuthenticator`` class you can extends to build your
fully customized authentication process. This class implements the Symfony ``SimplePreAuthenticatorInterface``
and ``AuthenticationFailureHandlerInterface``. Here is how to use it:

1. Implement the ``createToken`` method which handle the current request to build the token. for more information
 about this see the Symfony security component documentation.
2. Implement the ``onAuthenticationFailure`` which is called when an interactive authentication attempt fails.
This is called by authentication listeners inheriting from ``AbstractAuthenticationListener``.

The MukadiAPIAccessBundle provide also a default authenticator, named ``MacAuthenticator`` which use a `Hash MAC`
based signature to authenticate bundle. To use it, you have just to specify the hashing algorithm by implementing the
`getAlgorithm` method; and eventually the `onAuthenticationFailure` method. Let's build a API authenticator that extends
the MukadiAPIAccessBundle MacAuthenticator:

.. configuration-block::

    .. code-block:: php-annotations
    <?php
    // src/AppBundle/Security/Authenticator
    
    namespace AppBundle/Security;
    
    use Mukadi\APIAccessBundle\Security\Authenticator\MacAuthenticator;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    
    class Authenticator extends MacAuthenticator{
    
        public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
        {
            // Return a response in accordance with your API logic
            $resp = new Response(json_encode(array("ACK" => "ERROR","MESSAGE" => $exception->getMessage())));
            $resp->setStatusCode(403);
            $resp->headers->set('Content-Type','application/json');
            return $resp;
        }
        
        public function getAlgorithm()
        {
            // return the hash algorithm to use for MAC request signature
            return "sha256";
        }
    }

After building your API authenticator register it as service: 

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            my_api.authenticator:
                class: AppBundle\Security\Authenticator
                arguments: [@mukadi_api_access.api_client.client_manager]
                
The ApiAuthenticator children classes require the client manager service as argument.

Step 5: Configure your security layer
-------------------------------------

In order for Symfony's security component to use the MukadiAPIAccessBundle, you must
tell it to do so in the ``security.yml`` file. Below is a sample of the configuration
necessary to use the MukadiAPIAccessBundle in your application:

.. code-block:: yaml

    # app/config/security.yml
    security:
    #...
     providers:
         # register the client provider
         my_api_provider:
             id: mukadi_api_access.api_client.client_provider
     firewalls:
     #...
     api:
         pattern: ^/api
         stateless: true # for stateless API only
         simple_preauth:
             authenticator: my_api.authenticator # setting the authenticator
         provider: my_api_provider
         
For more information on configuring the ``security.yml`` file please read the Symfony
`security component documentation`_.

Advanced Topics
===============

Topic I: Using a custom Provider and Manager for API Client
-----------------------------------------------------------

When you create your client by extending a base MukadiAPIAccessClient you may want to
add your own logic, and want to add some custom logic to your client manager and/or provider.

For build your own client manager/provider just extends the ``ClientManager`` or implement the
``ClientManagerInterface``, register it as service and configure MukadiAPIAccessClient to use it
instead of the default one. Here is how your bundle configure may look like:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        mukadi_api_access:
            driver: orm # at the present version only orm value is supported
            clients: # register all clients you have created under this section
                my_api_client: # the value returned by the `getClientName` method
                    client_class: AppBundle\Entity\Client # the client class you have created
                    client_manager: your_custom_manager_service_id
                    client_provider: your_custom_provider_service_id
                    
