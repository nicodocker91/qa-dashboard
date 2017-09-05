Docker image to create dashboard about Quality Analytics
========================================================

## Usage

To launch the dashboard creation into the container, run:

    docker run --rm --user $(id -u):$(id -g) --volume ${BUILDS_LIST_FOLDER}:/data/build nicodocker91/qa-dashboard ${SERVICE_OR_BUNDLE_NAME}

## Requirements

In order to benefit to all advantages of the dashboard creation, you must have a **reports** folder with a specific architecture and file names that must match the expectation of tool results.

### Architecture

```
reports
│
└───project_name
    │
    └───...
    │
    └───date_of_build    # Like 201708311630 for the build reports executed the 31-08-2017 at 16:30.
    │
    └───current          # Symlink to the last build reports.
        │   
        └───logs
            │
            └───tool_1
            └───tool_2
            └───tool_3
            └───...    
```

### Tools

A whitelist of currently supported tools can be fond here. Please name the reports folder accordingly to the tool name described below.

#### pdepend

Into this folder, you must have the results of the reports of pdepend, named as described:

- `summary.xml` for the output file generated by the option `--summary-xml` of pdepend
- `pdepend.xml` for the output file generated by the option `--jdepend-xml` of pdepend
- `dependencies.svg` for the output file generated by the option `--jdepend-chart` of pdepend
- `overview-pyramid.svg` for the output file generated by the option `--overview-pyramid` of pdepend

#### phpcpd

Into this folder, you must have the results of the reports of phpcpd, named as described:

- `report.xml` for the output file generated by the option `--log-pmd` of phpcpd
- `percentage-report.txt` as the percentage of duplicated code the output of phpcpd can show you.
  - In order to fetch this percentage easily, simply `grep` it from the output and redirect it.
  - Ex: `phpcpd src | grep -Eo '[0-9.]+% duplicated lines out' | grep -Eo '[0-9.]+%' > percentage-report.txt`

#### phpcs

Into this folder, you must have the results of the reports of phpcs, named as described:

- `full-report.txt` for the output file generated by the option `--report-full` of phpcs
- `report.csv` for the output file generated by the option `--report-csv` of phpcs
- `report.xml` for the output file generated by the option `--report-xml` of phpcs
- `report.json` for the output file generated by the option `--report-json` of phpcs

#### phpmetrics

Simply run the `phpmetrics` tool by setting the `--report-html` option to this folder and let phpmetrics do the magic.

#### phpunit

Into this folder, you must have the results of the reports of phpunit, named as described:

- `phpunit-unit.xml` for the output file generated by the option `--log-junit` of phpunit
- `coverage-clover.xml` for the output file generated by the option `--coverage-clover` of phpunit
- `coverahe-html/` for the output **folder** generated by the option `--coverage-html` of phpunit

## Volumes

The only volume available to mount is `/data`.

The entrypoint is `/data/build`.

