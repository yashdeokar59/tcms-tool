#!/bin/bash

echo "🚀 Deploying TestFlow Pro to Kubernetes..."

# Create namespace
echo "📁 Creating namespace..."
kubectl apply -f k8s/namespace.yaml

# Deploy MySQL
echo "🗄️ Deploying MySQL database..."
kubectl apply -f k8s/mysql-deployment.yaml

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
kubectl wait --for=condition=ready pod -l app=mysql -n testflow-pro --timeout=300s

# Build Docker image
echo "🐳 Building Docker image..."
docker build -t testflow-pro:latest .

# Load image into kind cluster (if using kind)
if command -v kind &> /dev/null; then
    echo "📦 Loading image into kind cluster..."
    kind load docker-image testflow-pro:latest
fi

# Deploy application
echo "🚀 Deploying application..."
kubectl apply -f k8s/app-deployment.yaml

# Deploy ingress
echo "🌐 Setting up ingress..."
kubectl apply -f k8s/ingress.yaml

# Wait for application to be ready
echo "⏳ Waiting for application to be ready..."
kubectl wait --for=condition=ready pod -l app=testflow-pro-app -n testflow-pro --timeout=300s

# Get service URL
echo "🎉 Deployment complete!"
echo ""
echo "📋 Deployment Summary:"
echo "====================="
kubectl get pods -n testflow-pro
echo ""
kubectl get services -n testflow-pro
echo ""

# Check if LoadBalancer service is available
EXTERNAL_IP=$(kubectl get service testflow-pro-service -n testflow-pro -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null)
if [ -z "$EXTERNAL_IP" ]; then
    EXTERNAL_IP=$(kubectl get service testflow-pro-service -n testflow-pro -o jsonpath='{.status.loadBalancer.ingress[0].hostname}' 2>/dev/null)
fi

if [ -n "$EXTERNAL_IP" ]; then
    echo "🌐 Application URL: http://$EXTERNAL_IP"
else
    echo "🌐 To access the application, run:"
    echo "   kubectl port-forward service/testflow-pro-service 8080:80 -n testflow-pro"
    echo "   Then visit: http://localhost:8080"
fi

echo ""
echo "📊 To view logs:"
echo "   kubectl logs -f deployment/testflow-pro-app -n testflow-pro"
echo ""
echo "🔧 To access the database:"
echo "   kubectl port-forward service/mysql-service 3306:3306 -n testflow-pro"
