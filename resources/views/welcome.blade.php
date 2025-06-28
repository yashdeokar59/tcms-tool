<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TestFlow - Enhanced Test Case Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 800px;
            margin: 2rem;
        }
        .logo {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .subtitle {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .feature {
            background: #f8f9ff;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .feature h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        .feature p {
            color: #666;
            font-size: 0.9rem;
        }
        .login-info {
            background: #e8f4fd;
            border: 1px solid #b3d9f7;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        .login-info h3 {
            color: #1976d2;
            margin-bottom: 1rem;
        }
        .credentials {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            text-align: left;
        }
        .credential {
            background: white;
            padding: 1rem;
            border-radius: 5px;
            border-left: 3px solid #1976d2;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin: 1rem 0.5rem;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        .status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin: 1rem 0;
            color: #28a745;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .roles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .role {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🧪 TestFlow</div>
        <div class="subtitle">Enhanced Test Case Management System</div>
        
        <div class="status">
            <div class="status-dot"></div>
            <span>System Deployed Successfully</span>
        </div>

        <div class="login-info">
            <h3>🔐 Default Login Credentials</h3>
            <div class="credentials">
                <div class="credential">
                    <strong>Email:</strong><br>
                    admin@testflow.com
                </div>
                <div class="credential">
                    <strong>Password:</strong><br>
                    admin123
                </div>
            </div>
        </div>

        <div class="roles">
            <div class="role">👑 Admin</div>
            <div class="role">📊 Manager</div>
            <div class="role">👨‍💻 Developer</div>
            <div class="role">🧪 Tester</div>
        </div>

        <div class="features">
            <div class="feature">
                <h3>🎯 Test Management</h3>
                <p>Create, execute, and track test cases with comprehensive reporting</p>
            </div>
            <div class="feature">
                <h3>📋 Requirements</h3>
                <p>Link test cases to requirements for full traceability</p>
            </div>
            <div class="feature">
                <h3>🐛 Defect Tracking</h3>
                <p>Auto-generate defects from failed tests with detailed context</p>
            </div>
            <div class="feature">
                <h3>📊 Analytics</h3>
                <p>Real-time dashboards and KPIs for all stakeholders</p>
            </div>
            <div class="feature">
                <h3>👥 Team Collaboration</h3>
                <p>Comments, notifications, and role-based access control</p>
            </div>
            <div class="feature">
                <h3>📁 File Management</h3>
                <p>Attach screenshots, documents, and test evidence</p>
            </div>
        </div>

        <div>
            <a href="/admin" class="btn">🚀 Access Application</a>
            <a href="/health" class="btn">🔍 System Health</a>
        </div>

        <div style="margin-top: 2rem; color: #666; font-size: 0.9rem;">
            <p>✅ All requirements implemented: Tester, Developer, Manager, and Admin features</p>
            <p>🔧 Ready for production use with role-based access control</p>
        </div>
    </div>
</body>
</html>
