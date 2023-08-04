# Dkim Key pair

In order to sign our emails a private key must be created and stored on the server.
This private key must never be added to the git-history! Ask a product owner in case you need access to the key store.

The corresponding public key must be added to the DNS Dkim entry (Cloudflare).

Note: The private key must be kept valid during deployments.
