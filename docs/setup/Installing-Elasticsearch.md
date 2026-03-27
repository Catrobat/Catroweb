# Configure Elasticsearch on Ubuntu

```
curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo gpg --dearmor -o /usr/share/keyrings/elasticsearch-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/elasticsearch-keyring.gpg] https://artifacts.elastic.co/packages/8.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-8.x.list
sudo apt update
sudo apt install elasticsearch
# Disable security for local development (ES 8.x enables it by default)
# Replace if present, otherwise append
grep -q '^xpack.security.enabled' /etc/elasticsearch/elasticsearch.yml \
  && sudo sed -i 's/^xpack.security.enabled:.*/xpack.security.enabled: false/' /etc/elasticsearch/elasticsearch.yml \
  || echo 'xpack.security.enabled: false' | sudo tee -a /etc/elasticsearch/elasticsearch.yml
sudo systemctl start elasticsearch
sudo systemctl enable elasticsearch
```

### FOSElasticaBundle

- Run `php bin/console fos:elastica:populate` for creating indexes and `php bin/console fos:elastica:reset` for resetting indexes.

### Memory usage too high

```
sudo nano /etc/elasticsearch/jvm.options.d/jvm.options
```

```
-Xms1g
-Xmx1g
```

```
sudo systemctl restart elasticsearch
```
