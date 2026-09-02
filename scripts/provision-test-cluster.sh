#!/usr/bin/env bash

set -euo pipefail

KUBERNETES_VERSION="${KUBERNETES_VERSION:-v1.37.0}"
KUBERNETES_MINOR="${KUBERNETES_MINOR:-v1.37}"
FLANNEL_VERSION="${FLANNEL_VERSION:-v0.28.9}"
CERT_MANAGER_VERSION="${CERT_MANAGER_VERSION:-v1.21.1}"
APISERVER_EXTRA_SANS="${APISERVER_EXTRA_SANS:-127.0.0.1}"

export DEBIAN_FRONTEND=noninteractive

sudo apt-get update
sudo apt-get install -y \
    apt-transport-https \
    ca-certificates \
    containerd \
    curl \
    gpg

sudo install -d -m 0755 /etc/modules-load.d /etc/sysctl.d /etc/containerd
printf 'overlay\nbr_netfilter\n' | sudo tee /etc/modules-load.d/kubernetes.conf >/dev/null
sudo modprobe overlay
sudo modprobe br_netfilter
sudo tee /etc/sysctl.d/99-kubernetes-cri.conf >/dev/null <<'EOF'
net.bridge.bridge-nf-call-iptables = 1
net.bridge.bridge-nf-call-ip6tables = 1
net.ipv4.ip_forward = 1
EOF
sudo sysctl --system >/dev/null
sudo swapoff -a

containerd config default | sudo tee /etc/containerd/config.toml >/dev/null
sudo sed -i 's/SystemdCgroup = false/SystemdCgroup = true/' /etc/containerd/config.toml
sudo systemctl enable --now containerd
sudo systemctl restart containerd

sudo install -d -m 0755 /etc/apt/keyrings
curl -fsSL "https://pkgs.k8s.io/core:/stable:/${KUBERNETES_MINOR}/deb/Release.key" \
    | sudo gpg --dearmor --yes -o /etc/apt/keyrings/kubernetes-apt-keyring.gpg
echo "deb [signed-by=/etc/apt/keyrings/kubernetes-apt-keyring.gpg] https://pkgs.k8s.io/core:/stable:/${KUBERNETES_MINOR}/deb/ /" \
    | sudo tee /etc/apt/sources.list.d/kubernetes.list >/dev/null
sudo apt-get update
sudo apt-get install -y kubelet kubeadm kubectl
sudo apt-mark hold kubelet kubeadm kubectl >/dev/null
sudo systemctl enable --now kubelet

if [[ ! -f /etc/kubernetes/admin.conf ]]; then
    sudo kubeadm init \
        --kubernetes-version "${KUBERNETES_VERSION}" \
        --cri-socket unix:///run/containerd/containerd.sock \
        --apiserver-cert-extra-sans "${APISERVER_EXTRA_SANS}" \
        --pod-network-cidr 10.244.0.0/16
fi

install -d -m 0700 "${HOME}/.kube"
sudo install -m 0600 -o "$(id -u)" -g "$(id -g)" /etc/kubernetes/admin.conf "${HOME}/.kube/config"
kubectl taint nodes --all node-role.kubernetes.io/control-plane- >/dev/null 2>&1 || true
kubectl apply -f "https://github.com/flannel-io/flannel/releases/download/${FLANNEL_VERSION}/kube-flannel.yml"
kubectl rollout status daemonset/kube-flannel-ds -n kube-flannel --timeout=180s
kubectl wait --for=condition=Ready nodes --all --timeout=180s

kubectl apply -f "https://github.com/cert-manager/cert-manager/releases/download/${CERT_MANAGER_VERSION}/cert-manager.yaml"
kubectl rollout status deployment/cert-manager -n cert-manager --timeout=300s
kubectl rollout status deployment/cert-manager-cainjector -n cert-manager --timeout=300s
kubectl rollout status deployment/cert-manager-webhook -n cert-manager --timeout=300s
kubectl version
kubectl get nodes -o wide
kubectl get pods -A
