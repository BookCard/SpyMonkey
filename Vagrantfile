# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION="2"
VAGRANTBOX_IP="192.168.56.101"
VAGRANTBOX_HOSTNAME="localhost.dev"


Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "debian-lamp"
  config.vm.box_url = "https://www.dropbox.com/s/6qpt3jygoyucc92/debian-lamp.box"

  if Vagrant.has_plugin?("vagrant-hostmanager")
	config.hostmanager.enabled = true
	config.hostmanager.manage_host = true
	config.hostmanager.ignore_private_ip = false
	config.hostmanager.include_offline = true

	config.vm.define VAGRANTBOX_HOSTNAME do |node|
		node.vm.hostname = VAGRANTBOX_HOSTNAME
	    node.vm.network :private_network, ip: VAGRANTBOX_IP
	    node.hostmanager.aliases = %w(VAGRANTBOX_HOSTNAME)
	end
  else
	config.vm.network :private_network, ip: VAGRANTBOX_IP
  end

  config.vm.network :forwarded_port, guest: 80, host: 8080
  config.vm.network :forwarded_port, guest: 3306, host: 3306
  config.ssh.forward_agent = true
  config.vm.boot_timeout = 1000
  config.vm.synced_folder ".", "/vagrant", :id => "v-root"
  config.vm.synced_folder ".", "/var/www", :id => "v-data"

  config.vm.provider :virtualbox do |vb|
	vb.customize ["modifyvm", :id, "--memory", "512"]
	vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end
end
