Adding a new PHP version:

    for d in $(find tests -type d -name PHP_8.4); do cp -r "$d" $(dirname "$d")/PHP_8.5; done

    find tests -type f -path '*/PHP_8.5/*' -name README.md -exec sed -i 's/8\.4/8.5/g' \{\} \;
    find tests -type f -path '*/PHP_8.5/*' -name expected_output_template.txt -exec sed -i 's/8\.4/8.5/g' \{\} \;
    find tests -type f -path '*/PHP_8.5/*' -name Dockerfile -exec sed -i 's/8\.4/8.5/g' \{\} \;
    find tests -type f -path '*/PHP_8.5/*' -name composer.json -exec sed -i 's/8\.4/8.5/g' \{\} \;
    find tests -type f -path '*/PHP_8.5/*' -name composer.lock -delete

    find tests -type f -path '*/PHP_8.5/*' -name Dockerfile -exec sed -i '/^COPY.*composer.lock/s/^/# /' \{\} \;
    find tests -type f -path '*/PHP_8.5/*' -name Dockerfile -exec docker build . -f \{\} \;
    for d in $(find tests -type f -path '*/PHP_8.5/*' -name Dockerfile); do docker run --rm --entrypoint cat $(docker build -q . -f "$d") composer.lock > $(dirname "$d")/composer.lock; done
    find . -type f -path '*/PHP_8.5/*' -name Dockerfile -exec sed -i '/^# COPY.*composer.lock/s/^# //' \{\} \;

Adding a new PHPUnit version:

    cp -r tests/PHPUnit_12.2 tests/PHPUnit_13.0

    find tests/PHPUnit_13.0 -type f -name expected_output_template.txt -exec sed -i 's/12\.2/13.0/g' \{\} \;
    find tests/PHPUnit_13.0 -type f -name Dockerfile -exec sed -i 's/12\.2/13.0/g' \{\} \;
    find tests/PHPUnit_13.0 -type f -name composer.json -exec sed -i 's/12\.2/13.0/g' \{\} \;
    find tests/PHPUnit_13.0 -type f -name composer.lock -delete

    find tests/PHPUnit_13.0 -type f -name Dockerfile -exec sed -i '/^COPY.*composer.lock/s/^/# /' \{\} \;
    find tests/PHPUnit_13.0 -type f -name Dockerfile -exec docker build . -f \{\} \;
    for d in $(find tests/PHPUnit_13.0 -type f -name Dockerfile); do docker run --rm --entrypoint cat $(docker build -q . -f "$d") composer.lock > $(dirname "$d")/composer.lock; done
    find tests/PHPUnit_13.0 -type f -name Dockerfile -exec sed -i '/^# COPY.*composer.lock/s/^# //' \{\} \;
