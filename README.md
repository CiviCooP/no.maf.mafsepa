# no.maf.mafsepa

This extension contains customisations for the Civi Sepa extensions for MAF Norway.

## Word Replacements

When you enable this extension those word replacements are automatically added:

* Replace **CiviSepa Dashboard** with **Avtale Giro Dashboard**
* Replace **SEPA** with **Avtale Giro**

## Mandate reference

The mandate reference is hidden in the user interface as it is not needed

## EUR Changed to NOK

We only want to force the currency to NOK when the contribution recur or
the contribution is created by the user through the create mandate screen.
In all other scenario's there is no need to change the currency.

We do so by changing the currency in the pre hook.