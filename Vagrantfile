Vagrant.configure(2) do |config|
  config.vm.box = "CentOS-7.0"
  config.vm.box_url = "https://build.openconext.org/vagrant_boxes/virtualbox-centos7.box"

  config.vm.network "private_network", ip: "192.168.33.19"
  config.vm.hostname = "dev.support.surfconext.nl"
  config.hostsupdater.aliases = ["aa.dev.support.surfconext.nl","engine.dev.support.surfconext.nl","teams.dev.support.surfconext.nl","voot.dev.support.surfconext.nl","authz.dev.support.surfconext.nl","authz-admin.dev.support.surfconext.nl","aa.dev.support.surfconext.nl","mujina-idp.dev.support.surfconext.nl","manage.dev.support.surfconext.nl","spdashboard.dev.support.surfconext.nl"]
  config.vm.synced_folder ".", "/vagrant", :nfs => true
  config.vm.provider "virtualbox" do |v|
    v.customize ["modifyvm", :id, "--ioapic", "on"]
    v.customize ["modifyvm", :id, "--memory", "6072"]
  end
  
  config.vm.provision "ansible" do |ansible|
    ansible.playbook = "ansible/vagrant.yml"
    ansible.groups = {"dev" => "default"}
    ansible.extra_vars = {
      develop_spd: true
    }
  end

  # Stop/start Mailcatcher
  config.vm.provision :shell, run: "always", inline: "pkill mailcatcher || true"
  config.vm.provision :shell, run: "always", inline: "/usr/local/bin/mailcatcher --ip=0.0.0.0"
end
