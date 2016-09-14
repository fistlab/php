git subsplit init https://github.com/fistlab/php.git
git subsplit publish --heads="master" --no-tags src/Fist/Container:https://github.com/fistphp/container.git
git subsplit publish --heads="master" --no-tags src/Fist/Repository:https://github.com/fistphp/repository.git
git subsplit publish --heads="master" --no-tags src/Fist/Database:https://github.com/fistphp/database.git
rm -rf .subsplit/
