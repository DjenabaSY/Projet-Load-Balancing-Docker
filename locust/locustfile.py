from locust import HttpUser, task, between

class WebsiteUser(HttpUser):
    wait_time = between(1, 5)
    
    @task
    def test_app1(self):
        self.client.get("http://app1")
    
    @task
    def test_app2(self):
        self.client.get("http://app2")
