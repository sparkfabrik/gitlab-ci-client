# You can test the precunfigured command line enviroment running:
#
# make gitlab-ci-client
#

IMAGE_NAME ?= sparkfabrik/gitlab-ci-client
IMAGE_TAG ?= latest

gitlab-ci-client: build-docker-image
	@touch .env
	@docker run --rm  \
		-v ${PWD}/src:/var/www/html/src \
		--name spark-gitlab-ci-client-local \
		--env-file .env \
		-it $(IMAGE_NAME):$(IMAGE_TAG) $(GITLAB_ARGS)

build-docker-image:
	docker buildx build --load -t $(IMAGE_NAME):$(IMAGE_TAG) -f Dockerfile .