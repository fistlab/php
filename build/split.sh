git subsplit init https://github.com/fistlab/php.git
git subsplit publish --heads="master" --no-tags src/Fist/Container:https://github.com/fistphp/container.git
rm -rf .subsplit/
