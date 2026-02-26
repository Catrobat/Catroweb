# Configure Elasticsearch on Ubuntu

Check out this blog:
https://ourcodeworld.com/articles/read/1508/how-to-install-elasticsearch-7-in-ubuntu-2004

In summary:
```
curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-7.x.list
sudo apt update
sudo apt install elasticsearch
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
