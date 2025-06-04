<p align="center">
  <br />
    <img width="175" src="./art/Logo-Tilburg-University.png" alt="JetFly logo">
  <br />
</p>

<div align="center">
This repository is an online Research Artifact for the paper <em>The Influence of Sponsorship Funding on GitHub Organizations: A Staggered Difference-in-Differences Analysis</em>, for completion of the Master of Information Management at Tilburg University.
</div>

## Contents

The repository contains the following contents:

- `app`: a Laravel-application containing the data-collection and data-pipeline.
- `data`: an export of the dataset used in the paper as a SQL-file. The following tables are included:
  - `organizations`: a list of GitHub organizations
  - `repositories`: a list of repositories for all organizations
  - `repository_months`: for each repository, the monthly statistics
  - `organization_months`: for each organization, the monthly statistics, including aggregated statistics for all projects
  - `panel_data_05_05_2025`: the panel-data as used in the paper
  - `tags`: not used in the paper and incompelte, a list of tags for each repository 
                                                                
## Authors
- Ralph J. Smit
