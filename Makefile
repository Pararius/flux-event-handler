# Temporary Makefile while GitHub Actions is being fixed for public repos
# See https://github.community/t5/GitHub-Actions/GitHub-Actions-workflows-can-t-be-executed-on-this-repository/m-p/38153#M3236
test:
	docker-compose run --rm composer run-script test

build:
	docker build .

# Can only deploy with valid Docker login present on dev machine (repo admins only)
deploy:
	docker build -t treehouselabs/flux-event-handler:$(shell git rev-parse --short=8 HEAD) -t treehouselabs/flux-event-handler:latest .
	docker push treehouselabs/flux-event-handler
