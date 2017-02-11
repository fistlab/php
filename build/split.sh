#!/bin/bash

git subsplit init https://github.com/fistlab/php.git
git subsplit publish --heads="master" --no-tags src/Fist/Container:https://github.com/fistphp/container.git
git subsplit publish --heads="master" --no-tags src/Fist/Repository:https://github.com/fistphp/repository.git
git subsplit publish --heads="master" --no-tags src/Fist/Database:https://github.com/fistphp/database.git
git subsplit publish --heads="master" --no-tags src/Fist/Routing:https://github.com/fistphp/routing.git
git subsplit publish --heads="master" --no-tags src/Fist/Facade:https://github.com/fistphp/facade.git
git subsplit publish --heads="master" --no-tags src/Fist/Testing:https://github.com/fistphp/testing.git
rm -rf .subsplit/
