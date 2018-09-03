import os
from locust import HttpLocust, TaskSet, task

token = os.environ['AUTH_TOKEN']


class UserBehavior(TaskSet):

    @task(1)
    def rides(self):
        self.client.get("/api/v1/rides?page=1", headers={ "token": token })

    @task(1)
    def going_rides(self):
        self.client.get("/api/v1/rides?page=1&going=1", headers={ "token": token })

    @task(1)
    def profile(self):
        self.client.get("/api/v1/users/3", headers={ "token": token })

    @task(1)
    def my_rides(self):
        self.client.get("/api/v1/users/3/rides", headers={ "token": token })

    @task(1)
    def my_rides_history(self):
        self.client.get("/api/v1/users/3/rides/history", headers={ "token": token })
    
    @task(1)
    def ride_messages(self):
        self.client.get("/api/v1/rides/98969/messages", headers={ "token": token })

    @task(1)
    def places(self):
        self.client.get("/api/v1/places", headers={ "token": token })


class WebsiteUser(HttpLocust):
    task_set = UserBehavior
    min_wait = 500
    max_wait = 1000