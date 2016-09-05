git subsplit init git@github.com:fistlab/php.git
git subsplit publish --heads="master" --no-tags src/Fist/Container:git@github.com:fistphp/container.git
rm -rf .subsplit/
